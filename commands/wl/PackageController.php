<?php

namespace app\commands\wl;

use app\commands\Controller;
use app\extensions\Trackingmore;
use app\jobs\PackageRouteJob;
use app\modules\admin\modules\g\models\Package;
use Yii;
use yii\helpers\Console;
use yii\queue\Queue;

/**
 * 包裹数据处理
 *
 * @package app\commands
 */
class PackageController extends Controller
{

    /**
     * 每次处理数量
     */
    const PAGE_SIZE = 100;

    /**
     * 将要处理的包裹加入队列
     *
     * @param null|string $packageId 待处理的包裹 id，多个之间使用小写的逗号进行分隔
     * @throws \yii\db\Exception
     */
    public function actionAddJob($packageId = null)
    {
        $this->stdout("Begin..." . PHP_EOL);
        $packageId = array_filter(explode(",", $packageId));
        /* @var $queue Queue */
        $queue = Yii::$app->queue;
        $db = Yii::$app->getDb();
        $totalCount = $packageId ? count($packageId) : $db->createCommand("SELECT COUNT(*) FROM {{%g_package}} WHERE [[status]] <> :status", [':status' => Package::STATUS_SUCCESSFUL_RECEIPTED])->queryScalar();
        $pages = (int) (($totalCount + self::PAGE_SIZE - 1) / self::PAGE_SIZE);
        for ($page = 1; $page <= $pages; $page++) {
            $sql = "SELECT [[id]], [[logistics_query_raw_results]] FROM {{%g_package}}";
            $conditions = ['[[status]] <> :status'];
            $params = [
                ':status' => Package::STATUS_SUCCESSFUL_RECEIPTED,
            ];
            $limit = "";
            if ($packageId) {
                $conditions[] = '[[id]] IN (' . implode(', ', $packageId) . ')';
            } else {
                $offset = ($page - 1) * self::PAGE_SIZE;
                $limit = "LIMIT :offset, :limit";
                $params[':offset'] = $offset;
                $params[':limit'] = self::PAGE_SIZE;
            }
            $sql .= " WHERE " . implode(' AND ', $conditions);
            if ($limit) {
                $sql .= " $limit";
            }
            $packages = $db->createCommand($sql, $params)->queryAll();
            foreach ($packages as $package) {
                if ($package['logistics_query_raw_results'] != '[]') {
                    $this->stdout(" > Add package #{$package['id']} job." . PHP_EOL);
                    $queue->push(new PackageRouteJob([
                        'id' => $package['id'],
                    ]));
                } else {
                    $this->stdout(" > Package #{$package['id']} Non logistics information, ignore." . PHP_EOL);
                }
            }
        }

        $this->stdout("Done.");
    }

    /**
     * 物流单号创建以及查询
     *
     * @param $trackingNumberArr
     * @param Trackingmore $track
     * @param $data
     * @return array
     */
    private function trackCreateSelect($trackingNumberArr, Trackingmore $track, $data)
    {
        $items = [];
        $trackCreates = $track->createMultipleTracking($data);
        if (isset($trackCreates['meta']['code']) && in_array($trackCreates['meta']['code'], [200, 201])) {
            $this->stdout("   >> ADD TO trackingmore.com [ SUCCESSFUL ]" . PHP_EOL);
            $trackingNumber = implode(',', $trackingNumberArr);
            sleep(3);
            // 获取刚添加的
            $trackInfoList = $track->getTrackingsList($trackingNumber, '', 1, 100, 0, 0, 0, 0, 0, 0, 'en');
            if (isset($trackInfoList['data']['items'])) {
                foreach ($trackInfoList['data']['items'] as $trackInfo) {
                    if (isset($trackInfo['origin_info']['trackinfo'])) {
                        foreach ($trackInfo['origin_info']['trackinfo'] as $item) {
                            $status = $item['checkpoint_status'] ?? null;
                            $status = $status !== null ? $status : ($item['substatus'] ?? null);
                            $items[$trackInfo['tracking_number']][] = [
                                'datetime' => $item['Date'],
                                'description' => $item['StatusDescription'],
                                'status' => $status,
                            ];
                        }
                    }
                }
            }
        } else {
            $message = $trackCreates['meta']['message'] ?? null;
            $message || isset($trackCreates['meta']) ? var_export($trackCreates['meta'], true) : null;
            if ($message) {
                $this->stdout(" >>> " . $message);
            }
        }

        return $items;
    }

    /**
     * 物流信息查询
     *
     * @throws \yii\db\Exception
     */
    public function actionRoute()
    {
        $this->stdout("Begin..." . PHP_EOL);
        $db = Yii::$app->getDb();
        $queue = Yii::$app->queue;
        $cmd = $db->createCommand();
        // 物流单号查询接口
        $track = new Trackingmore();
        // 获取总数
        $totalCount = $db->createCommand("SELECT COUNT(*) FROM {{%g_package}} WHERE [[status]] <> :status", [':status' => Package::STATUS_SUCCESSFUL_RECEIPTED])->queryScalar();
        $totalPages = (int) (($totalCount + self::PAGE_SIZE - 1) / self::PAGE_SIZE);
        // 获取运单号代码
        $companyCode = $db->createCommand("SELECT [[c.id]], [[cl.id]] AS [[line_id]], [[c.code]] FROM {{%wuliu_company_line}} cl LEFT JOIN {{%wuliu_company}} c on [[cl.company_id]] = [[c.id]]")->queryAll();
        $totalPackages = 0;
        $createPackages = 0; // 新增多少个订单
        $notFoundPackages = 0; // 查询不到的订单
        Console::startProgress(0, $totalPages);
        for ($page = 1; $page <= $totalPages; $page++) {
            $this->stdout("Page: $page/$totalPages" . PHP_EOL);
            $packages = $db->createCommand("SELECT * FROM {{%g_package}} WHERE [[status]] <> :status LIMIT :offset, :limit", [
                ':status' => Package::STATUS_SUCCESSFUL_RECEIPTED,
                ':offset' => ($page - 1) * self::PAGE_SIZE,
                ':limit' => self::PAGE_SIZE,
            ])->queryAll();
            // 查询物流信息，多订单查询
            $waybillNumbers = [];
            $trackingsList = [];
            foreach ($packages as $package) {
                // 存入所有订单数据
                $waybillNumbers[$package['waybill_number']] = [
                    'number' => $package['waybill_number'],
                    'line_id' => $package['logistics_line_id'],
                ];
                $trackingsList[$package['waybill_number']] = $package['waybill_number'];
            }

            if ($waybillNumbers) {
                $notFoundWaybillNumbers = [];
                sleep(1);
                // 获取订单所有信息
                $trackInfoList = $track->getTrackingsList(implode(',', $trackingsList), '', 1, 100, 0, 0, 0, 0, 0, 0, 'en');
                if ($trackInfoList) {
                    if ($trackInfoList['meta']['code'] == 200) {
                        if ($trackInfoList['data']['items']) {
                            foreach ($packages as $key => $package) {
                                $packages[$key]['result'] = [];
                                foreach ($trackInfoList['data']['items'] as $trackInfo) {
                                    if ($package['waybill_number'] == $trackInfo['tracking_number']) {
                                        if (isset($trackInfo['origin_info']['trackinfo']) && is_array($trackInfo['origin_info']['trackinfo'])) {
                                            $this->stdout(" > {$package['waybill_number']} [ " . Console::ansiFormat("FOUND", [Console::BG_GREEN]) . " ]" . PHP_EOL);
                                            unset($waybillNumbers[$trackInfo['tracking_number']]);
                                            unset($trackingsList[$trackInfo['tracking_number']]);
                                            foreach ($trackInfo['origin_info']['trackinfo'] as $item) {
                                                $status = $item['checkpoint_status'] ?? null;
                                                $status = $status !== null ? $status : ($item['substatus'] ?? null);
                                                $totalPackages++;
                                                $packages[$key]['result'][] = [
                                                    'datetime' => $item['Date'],
                                                    'description' => $item['StatusDescription'],
                                                    'status' => $status,
                                                ];
                                            }
                                        } else {
                                            $this->stdout(" > {$package['waybill_number']} [ " . Console::ansiFormat("NOT FOUND", [Console::BG_RED]) . " ]." . PHP_EOL);
                                        }
                                        break;
                                    }
                                }
                            }
                            $notFoundWaybillNumbers = $waybillNumbers;
                        }
                    } elseif ($trackInfoList['meta']['code'] == 4031) {
                        $notFoundWaybillNumbers = $waybillNumbers;
                    } else {
                        Yii::error($trackInfoList['meta']['message']);
                        $this->stdout($trackInfoList['meta']['message'] . PHP_EOL);
                    }
                }

                if ($notFoundWaybillNumbers) {
                    // 全部都没添加，则添加数据
                    $createData = [];
                    foreach ($notFoundWaybillNumbers as $waybillNumber) {
                        foreach ($companyCode as $item) {
                            if ($waybillNumber['line_id'] == $item['line_id']) {
                                $createData[] = [
                                    'tracking_number' => $waybillNumber['number'],
                                    'carrier_code' => $item['code'],
                                    'order_id' => $waybillNumber['number'],
                                    'lang' => 'en',
                                ];
                                $createPackages++;
                                $this->stdout(" > {$waybillNumber['number']} [ ADD TO trackingmore.com ]" . PHP_EOL);
                                break;
                            }
                        }
                    }
                    if ($createData) {
                        $items = $this->trackCreateSelect($trackingsList, $track, $createData);
                        foreach ($packages as $packageKey => $package) {
                            foreach ($items as $key => $item) {
                                if ($key == $package['waybill_number']) {
                                    $packages[$packageKey]['result'] = $item;
                                    $this->stdout(" > {$package['waybill_number']} [ FOUND ]" . PHP_EOL);
                                    break;
                                }
                            }
                        }
                    }
                }
                foreach ($packages as $package) {
                    $logisticsQueryRawResults = $package['logistics_query_raw_results'];
                    $logisticsQueryRawResults = $logisticsQueryRawResults ? json_decode($logisticsQueryRawResults, true) : [];

                    if (isset($package['result']) && $package['result'] && count($package['result']) > count($logisticsQueryRawResults)) {
                        $cmd->update('{{%g_package}}', [
                            'logistics_query_raw_results' => json_encode($package['result']),
                            'logistics_last_check_datetime' => time(),
                        ], ['id' => $package['id']])->execute();
                        $queue->push(new PackageRouteJob([
                            'id' => $package['id'],
                        ]));
                        $totalPackages++;
                        $this->stdout($package['waybill_number'] . " Update Query result to DB." . PHP_EOL);
                    } elseif ((!isset($package['result']) || !$package['result']) && $package['status'] == Package::STATUS_PENDING) {
                        // 如果没有result 并且状态是待处理状态,修改为查询不到
                        $cmd->update('{{%g_package}}', [
                            'status' => Package::STATUS_NOT_FOUND,
                            'logistics_last_check_datetime' => time(),
                        ], ['id' => $package['id']])->execute();
                        $notFoundPackages++;
                    }
                }
            }
            $this->stdout(PHP_EOL);
            Console::updateProgress($page, $totalPages);
            $this->stdout(PHP_EOL);
        }
        Console::endProgress();
        $this->stdout("Done, Total: {$totalPackages}, Add to trackingmore.com: {$createPackages}, Not Found: {$notFoundPackages}.");
    }
}

<?php

namespace app\commands\g;

use app\commands\Controller;
use app\extensions\Trackingmore;
use app\jobs\PackageRouteJob;
use app\modules\admin\modules\g\models\Package;
use Yii;
use yii\db\Query;
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
        $n = 0;
        $totalCount = $packageId ? count($packageId) : $db->createCommand("SELECT COUNT(*) FROM {{%g_package}} WHERE [[status]] <> :status AND [[waybill_number]] IS NOT NULL AND [[waybill_number]] != ''", [':status' => Package::STATUS_SUCCESSFUL_RECEIPTED])->queryScalar();
        $pages = (int) (($totalCount + self::PAGE_SIZE - 1) / self::PAGE_SIZE);
        for ($page = 1; $page <= $pages; $page++) {
            $sql = "SELECT [[id]], [[logistics_query_raw_results]] FROM {{%g_package}}";
            $conditions = ["[[status]] <> :status AND [[waybill_number]] IS NOT NULL AND [[waybill_number]] != ''"];
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
                    $n++;
                } else {
                    $this->stdout(" > Package #{$package['id']} Non logistics information, ignore." . PHP_EOL);
                }
            }
        }

        $this->stdout("Total add $n jobs." . PHP_EOL);
        $this->stdout("Done.");
    }

    /**
     * 物流单号创建
     *
     * @param Trackingmore $track
     * @param $data
     * @param string $numbers
     */
    private function addTrackingData(Trackingmore $track, $data, $numbers = '')
    {
        $this->stdout(" >> [ $numbers ] to trackingmore.com ");
        $trackCreates = $track->createMultipleTracking($data);
        if (isset($trackCreates['meta']['code']) && in_array($trackCreates['meta']['code'], [200, 201])) {
            $this->stdout(" [ SUCCESSFUL ]");
        } else {
            $this->stdout(" [ FAIL ], Error: " . $trackCreates['meta']['message']);
        }
        $this->stdout(PHP_EOL);
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
        $cmd = $db->createCommand();
        /* @var $queue Queue */
        $queue = Yii::$app->queue;
        // 物流单号查询接口
        $track = new Trackingmore();
        $sizePerTime = 30; // 每次处理的数量
        // 获取总数
        $totalCount = $db->createCommand("SELECT COUNT(*) FROM {{%g_package}} WHERE [[status]] <> :status AND [[waybill_number]] IS NOT NULL AND [[waybill_number]] != ''", [':status' => Package::STATUS_SUCCESSFUL_RECEIPTED])->queryScalar();
        $totalPages = (int) (($totalCount + $sizePerTime - 1) / $sizePerTime);
        // 物流公司及其代码
        $companyLines = (new Query())
            ->select(['c.code'])
            ->from('{{%wuliu_company_line}} t')
            ->leftJoin('{{%wuliu_company}} c', '[[t.company_id]] = [[c.id]]')
            ->indexBy('t.id')
            ->column();
        $totalPackages = 0;
        $createPackages = 0; // 新增多少个订单
        $notFoundPackages = 0; // 查询不到的订单
        Console::startProgress(0, $totalPages);
        for ($page = 1; $page <= $totalPages; $page++) {
            $packages = $db->createCommand("SELECT [[id]], [[waybill_number]], [[logistics_line_id]], [[status]], [[logistics_query_raw_results]] FROM {{%g_package}} WHERE [[status]] <> :status AND [[waybill_number]] IS NOT NULL AND [[waybill_number]] != '' LIMIT :offset, :limit", [
                ':status' => Package::STATUS_SUCCESSFUL_RECEIPTED,
                ':offset' => ($page - 1) * $sizePerTime,
                ':limit' => $sizePerTime,
            ])->queryAll();
            // 查询物流信息，多订单查询
            $waybillNumbers = [];
            foreach ($packages as $package) {
                // 存入所有订单数据
                $waybillNumbers[$package['waybill_number']] = [
                    'number' => $package['waybill_number'],
                    'line_id' => $package['logistics_line_id'],
                ];
            }

            if ($waybillNumbers) {
                $notFoundWaybillNumbers = [];
                sleep(1);
                // 获取订单所有信息
                $trackInfoList = $track->getTrackingsList(implode(',', array_keys($waybillNumbers)), '', 1, 100, 0, 0, 0, 0, 0, 0, 'en');
                $statusCode = $trackInfoList['meta']['code'] ?? null;
                if ($statusCode == 200) {
                    $trackInfoListItems = $trackInfoList['data']['items'] ?? [];
                    if ($trackInfoListItems) {
                        foreach ($packages as $key => $package) {
                            $results = [];
                            foreach ($trackInfoListItems as $trackInfo) {
                                if ($package['waybill_number'] == $trackInfo['tracking_number'] &&
                                    isset($trackInfo['origin_info']['trackinfo']) && is_array($trackInfo['origin_info']['trackinfo'])
                                ) {
                                    $this->stdout(" > {$package['waybill_number']} [ " . Console::ansiFormat("FOUND", [Console::BG_GREEN]) . " ]" . PHP_EOL);
                                    foreach ($trackInfo['origin_info']['trackinfo'] as $item) {
                                        $results[] = [
                                            'datetime' => $item['Date'],
                                            'description' => $item['StatusDescription'],
                                            'status' => $item['checkpoint_status'],
                                        ];
                                    }

                                    break;
                                }
                            }
                            $logisticsQueryRawResults = $package['logistics_query_raw_results'];
                            $logisticsQueryRawResults && $logisticsQueryRawResults = json_decode($logisticsQueryRawResults, true);
                            $logisticsQueryRawResults = $logisticsQueryRawResults ?: [];
                            if (count($results) > count($logisticsQueryRawResults)) {
                                $cmd->update('{{%g_package}}', [
                                    'logistics_query_raw_results' => json_encode($results)
                                ], ['id' => $package['id']])->execute();
                                $queue->push(new PackageRouteJob([
                                    'id' => $package['id'],
                                ]));
                                $totalPackages++;
                            } elseif (!$results && $package['status'] == Package::STATUS_PENDING) {
                                // 如果没有 result 并且状态是待处理状态，修改为查询不到
                                $cmd->update('{{%g_package}}', [
                                    'status' => Package::STATUS_NOT_FOUND
                                ], ['id' => $package['id']])->execute();
                                $notFoundWaybillNumbers[] = $waybillNumbers[$package['waybill_number']];
                            }
                        }
                    }
                } elseif ($statusCode == 4031) {
                    $notFoundWaybillNumbers = $waybillNumbers;
                } else {
                    Yii::error($trackInfoList['meta']['message']);
                    $this->stdout($trackInfoList['meta']['message'] . PHP_EOL);
                }

                if ($notFoundWaybillNumbers) {
                    $numbers = [];
                    $payload = [];
                    foreach ($notFoundWaybillNumbers as $waybillNumber) {
                        if (isset($companyLines[$waybillNumber['line_id']])) {
                            $numbers[] = $waybillNumber['number'];
                            $payload[] = [
                                'tracking_number' => $waybillNumber['number'],
                                'carrier_code' => $companyLines[$waybillNumber['line_id']],
                                'order_id' => $waybillNumber['number'],
                                'lang' => 'en',
                            ];
                            $createPackages++;
                        }
                    }
                    $payload && $this->addTrackingData($track, $payload, implode(', ', $numbers));
                }
            }
            Console::updateProgress($page, $totalPages);
        }
        Console::endProgress();
        $this->stdout("Done, Total: {$totalPackages}, Add to trackingmore.com: {$createPackages}, Not Found: {$notFoundPackages}.");
    }

}

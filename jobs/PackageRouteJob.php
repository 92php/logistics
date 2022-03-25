<?php

namespace app\jobs;

use app\models\Constant;
use app\modules\admin\modules\wuliu\models\PackageRoute;
use DateTime;
use Yii;
use yii\queue\JobInterface;
use function Symfony\Component\String\u;

/**
 * 包裹物流路由检测任务
 *
 * @package app\jobs
 */
class PackageRouteJob extends Job implements JobInterface
{

    /**
     * 路由检测方式
     */
    const ROUTE_CHECK_BY_DAY = 'day';
    const ROUTE_CHECK_IGNORE = 'ignore';
    const ROUTE_CHECK_BY_AUTO = 'auto';

    /**
     * @var int 包裹 id
     */
    public $id;

    /**
     * @param $queue
     * @throws \yii\db\Exception
     * @throws \Exception
     */
    public function execute($queue)
    {
        $messages = [""];
        $db = Yii::$app->getDb();
        $package = $db->createCommand("SELECT [[id]], [[logistics_line_id]], [[number]], [[waybill_number]], [[delivery_datetime]], [[logistics_query_raw_results]] FROM {{%g_package}} WHERE id = :id AND [[logistics_line_id]] <> 0", [':id' => (int) $this->id])->queryOne();
        if ($package) {
            $cmd = $db->createCommand();
            $now = time();
            $logisticsQueryRawResults = json_decode($package['logistics_query_raw_results'], true);
            !$logisticsQueryRawResults && $logisticsQueryRawResults = [];
            $messages[] = "编号：{$package['id']} 包裹号：{$package['number']}  运单号：{$package['waybill_number']}  发货时间：" . date('Y-m-d H:i:s', $package['delivery_datetime']);
            $lineRoutes = $db->createCommand('SELECT [[id]], [[step]], [[event]], [[detection_keyword]], [[estimate_days]], [[package_status]] FROM {{%wuliu_company_line_route}} WHERE [[line_id]] = :lineId AND [[enabled]] = :enabled ORDER BY [[step]] ASC', [
                ':lineId' => $package['logistics_line_id'],
                ':enabled' => Constant::BOOLEAN_TRUE,
            ])->queryAll();
            $packageId = $package['id'];
            $deliveryDatetime = (new DateTime())->setTimestamp($package['delivery_datetime']); // 发货时间
            $lineRoutePackageStatus = 0;
            $offset = 1;
            $packageIsFinished = false;
            foreach ($lineRoutes as $key => $route) {
                switch ($route['estimate_days']) {
                    case 0:
                        $routeCheckBy = self::ROUTE_CHECK_BY_AUTO;
                        break;

                    case -1:
                        $routeCheckBy = self::ROUTE_CHECK_IGNORE;
                        break;

                    default:
                        $routeCheckBy = self::ROUTE_CHECK_BY_DAY;
                        break;
                }
                $detectionKeywords = explode('||', $route['detection_keyword']); // 多个路由检测点以 || 进行分隔
                $found = false;
                $foundDatetime = null;
                foreach ($logisticsQueryRawResults as $i => $result) {
                    if (!isset($logisticsQueryRawResults[$i]['_found'])) {
                        $logisticsQueryRawResults[$i]['_found'] = false;
                        $logisticsQueryRawResults[$i]['_detectionKeyword'] = null;
                    }
                    $description = str_replace("  ", ' ', $result['description']);
                    foreach ($detectionKeywords as $detectionKeyword) {
                        if (mb_strpos($description, $detectionKeyword) !== false) {
                            $found = true;
                            $route['package_status'] && $lineRoutePackageStatus = $route['package_status'];
                            $foundDatetime = $result['datetime'];
                            $logisticsQueryRawResults[$i]['_found'] = true;
                            $logisticsQueryRawResults[$i]['_detectionKeyword'] = $detectionKeyword;
                            $logisticsQueryRawResults[$i]['_step'] = $route['step'];
                            break; // 只找最近的一个，其他的丢弃
                        }
                    }
                }
                if ($found) {
                    $packageIsFinished = true;
                } elseif ($routeCheckBy != self::ROUTE_CHECK_IGNORE) {
                    $packageIsFinished = false;
                }
                $lineRoutes[$key]['exists'] = $found;
                if ($routeCheckBy == self::ROUTE_CHECK_IGNORE && !$found) {
                    $offset++; // 不进行预估的节点会忽略，所以需要记录最后一个需要预估的节点下标偏移值
                    continue;
                }
                // 时间计算
                $beginDatetime = null;
                if ($key == 0) {
                    // 如果是第一步路由点则取发货时间
                    $beginDatetime = clone $deliveryDatetime;
                } else {
                    $prevKey = $key - $offset;
                    if (isset($lineRoutes[$prevKey]) && $lineRoutes[$prevKey]['end_datetime']) {
                        $beginDatetime = (new DateTime())->setTimestamp($lineRoutes[$prevKey]['end_datetime']);
                    }
                }
                $planDatetime = clone $deliveryDatetime; // 预计时间
                $prevLineRoutes = array_filter($lineRoutes, function ($v) use ($route) {
                    return $v['step'] <= $route['step'];
                }, ARRAY_FILTER_USE_BOTH);
                $hours = 0;
                foreach ($prevLineRoutes as $prevLineRoute) {
                    if (isset($prevLineRoute['end_datetime']) && $prevLineRoute['end_datetime']) {
                        // 如果前一步有检测到，应该在前一步的抵达时间上增加
                        $hours = 0;
                        $planDatetime = (new DateTime())->setTimestamp($prevLineRoute['end_datetime']);
                        continue;
                    } elseif (isset($prevLineRoute['plan_datetime']) && $prevLineRoute['plan_datetime']) {
                        $hours = 0;
                        $planDatetime = (new DateTime())->setTimestamp($prevLineRoute['plan_datetime']);
                        continue;
                    }

                    if ($prevLineRoute['estimate_days'] > 0) {
                        $hours += $prevLineRoute['estimate_days'] * 24;
                    } elseif ($prevLineRoute['estimate_days'] == 0) {
                        // 未设置预计天数，使用系统自动预估（使用正常、延期、提前三种状态的数据参与计算）
                        $size = 1000;
                        $pickPackages = $db->createCommand("SELECT [[begin_datetime]], [[end_datetime]] FROM {{%wuliu_package_route}} WHERE [[line_route_id]] = :lineRouteId AND [[status]] IN (1, 2, 3) ORDER BY [[id]] DESC LIMIT $size", [
                            ':lineRouteId' => $prevLineRoute['id'],
                        ])->queryAll();
                        $totalSeconds = 0;
                        foreach ($pickPackages as $pickPackage) {
                            $totalSeconds += $pickPackage['end_datetime'] - $pickPackage['begin_datetime'];
                        }
                        if ($totalSeconds) {
                            $hours += round($totalSeconds / 3600) / count($pickPackages);
                        }
                    }
                }
                if ($hours) {
                    $planDatetime->modify("+$hours hours");
                }

                $fnDatetimeDiffDays = function (DateTime $date1, $date2) {
                    return $date1->diff($date2)->days;
                };
                if ($found) {
                    // 找到了物流信息
                    $endDatetime = new DateTime($foundDatetime);
                    $days = $fnDatetimeDiffDays($planDatetime, $endDatetime);
                    if ($days == 0) {
                        $status = PackageRoute::STATUS_NORMAL;
                    } elseif ($days > 0) {
                        $status = PackageRoute::STATUS_OVERTIME;
                    } else {
                        $status = PackageRoute::STATUS_IN_ADVANCE;
                    }
                } else {
                    // 未找到物流信息
                    $endDatetime = null;
                    $days = $fnDatetimeDiffDays($planDatetime, new DateTime());
                    if ($days == 0) {
                        $status = PackageRoute::STATUS_MAYBE_NORMAL;
                    } elseif ($days > 0) {
                        $status = PackageRoute::STATUS_MAYBE_OVERTIME;
                    } else {
                        $status = PackageRoute::STATUS_MAYBE_IN_ADVANCE;
                    }
                }
                if ((!$hours && $routeCheckBy != self::ROUTE_CHECK_BY_AUTO) || $routeCheckBy == self::ROUTE_CHECK_IGNORE) {
                    $status = PackageRoute::STATUS_UNKNOWN;
                }

                switch ($status) {
                    case PackageRoute::STATUS_NORMAL:
                    case PackageRoute::STATUS_MAYBE_NORMAL:
                    case PackageRoute::STATUS_UNKNOWN:
                        $processStatus = PackageRoute::PROCESS_STATUS_NOTHING;
                        break;

                    default:
                        $processStatus = PackageRoute::PROCESS_STATUS_PENDING;
                        break;
                }

                switch ($routeCheckBy) {
                    case self::ROUTE_CHECK_BY_DAY:
                        $computeMethod = PackageRoute::COMPUTE_METHOD_MANUAL;
                        break;

                    case self::ROUTE_CHECK_BY_AUTO:
                        $computeMethod = PackageRoute::COMPUTE_METHOD_AUTO;
                        break;

                    default:
                        $computeMethod = PackageRoute::COMPUTE_METHOD_NONE;
                        break;
                }
                $columns = [
                    'compute_method' => $computeMethod,
                    'compute_reference_value' => $computeMethod == PackageRoute::COMPUTE_METHOD_MANUAL ? $route['estimate_days'] : 0,
                    'begin_datetime' => $beginDatetime ? $beginDatetime->getTimestamp() : null,
                    'process_status' => $processStatus,
                ];

                if ($beginDatetime && $endDatetime && $routeCheckBy != self::ROUTE_CHECK_IGNORE) {
                    $takeMinutes = floor(($endDatetime->getTimestamp() - $beginDatetime->getTimestamp()) / 60);
                } else {
                    $takeMinutes = 0;
                }
                $columns['take_minutes'] = $takeMinutes;
                $packageRoute = $db->createCommand('SELECT [[id]], [[status]], [[plan_datetime]], [[plan_datetime_is_changed]] FROM {{%wuliu_package_route}} WHERE [[package_id]] = :packageId AND [[line_route_id]] = :lineRouteId', [
                    ':packageId' => $package['id'],
                    ':lineRouteId' => $route['id'],
                ])->queryOne();
                $fnSetNullDatetime = function ($columns) {
                    foreach (['begin_datetime', 'plan_datetime'] as $key) {
                        isset($columns[$key]) && $columns[$key] = null;
                    }

                    return $columns;
                };
                if ($packageRoute) {
                    $columns = array_merge($columns, [
                        'end_datetime' => $endDatetime ? $endDatetime->getTimestamp() : null,
                        'status' => $status,
                    ]);
                    if (!in_array($packageRoute['status'], [PackageRoute::STATUS_NORMAL, PackageRoute::STATUS_OVERTIME, PackageRoute::STATUS_IN_ADVANCE]) && !$packageRoute['plan_datetime_is_changed']) {
                        $columns['plan_datetime'] = $planDatetime->getTimestamp();
                    }
                    $routeCheckBy == self::ROUTE_CHECK_IGNORE && $columns = $fnSetNullDatetime($columns);
                    $cmd->update('{{%wuliu_package_route}}', $columns, ['id' => $packageRoute['id']])->execute();
                    if ($packageRoute['plan_datetime_is_changed']) {
                        $lineRoutes[$key]['plan_datetime'] = $packageRoute['plan_datetime'];
                    }
                } else {
                    $columns = array_merge($columns, [
                        'package_id' => $packageId,
                        'line_route_id' => $route['id'],
                        'plan_datetime' => $planDatetime ? $planDatetime->getTimestamp() : null,
                        'end_datetime' => $endDatetime ? $endDatetime->getTimestamp() : null,
                        'status' => $status,
                    ]);
                    $routeCheckBy == self::ROUTE_CHECK_IGNORE && $columns = $fnSetNullDatetime($columns);
                    $cmd->insert('{{%wuliu_package_route}}', $columns)->execute();
                }

                $lineRoutes[$key]['end_datetime'] = $endDatetime ? $endDatetime->getTimestamp() : null;
            }
            // 更新包裹状态
            $columns = [
                'logistics_last_check_datetime' => $now,
            ];
            $lineRoutePackageStatus && $columns['status'] = $lineRoutePackageStatus;
            $cmd->update('{{%g_package}}', $columns, ['id' => $package['id']])->execute();

            // 整理输出日志
            $messages[] = u(' 路由检测结果 ')->padBoth(80, '=')->toString();
            $steps = ['❶', '❷', '❸', '❹', '❺', '❻', '❼', '❽', '❾', '❿'];
            foreach ($logisticsQueryRawResults as $result) {
                $found = isset($result['_found']) && $result['_found'];
                $foundText = $found ? '✔' : '✘';
                $description = $result['description'];
                $step = ' ';
                if ($found) {
                    $step = isset($steps[$result['_step'] - 1]) ? $steps[$result['_step'] - 1] : '?';
                    $description = str_replace($result['_detectionKeyword'], ">>>{$result['_detectionKeyword']}<<<", $description);
                }
                $messages[] = "$step $foundText {$result['datetime']} $description 【{$result['status']}】";
            }
            $notFoundMessages = [];
            foreach ($lineRoutes as $route) {
                if ($route['exists'] === false) {
                    $notFoundMessages[] = (isset($steps[$route['step'] - 1]) ? $steps[$route['step'] - 1] : '?') . " {$route['event']} => {$route['detection_keyword']}";
                }
            }
            if ($notFoundMessages) {
                $messages[] = u(' 未检测到的路由 ')->padBoth(80, '-')->toString();
                $messages = array_merge($messages, $notFoundMessages);
            }
            $messages[] = str_repeat('=', 80);
        } else {
            $messages[] = "包裹 #{$this->id} 不存在、或者未设置物流线路。";
        }
        $this->info(implode(PHP_EOL, $messages));
    }

}
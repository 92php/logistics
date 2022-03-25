<?php

namespace app\modules\api\modules\g\controllers;

use app\modules\api\modules\g\extensions\Formatter;
use app\modules\api\modules\g\models\Order;
use app\modules\api\modules\g\models\OrderSearch;
use app\modules\api\modules\g\models\Shop;
use DateTime;
use Exception;
use Yii;
use yii\db\Query;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use function sort;

/**
 * /api/g/order/
 * 公共订单接口
 *
 * @package app\modules\api\modules\g\controllers
 */
class OrderController extends Controller
{

    public $modelClass = Order::class;

    public function actions()
    {
        $actions = parent::actions();
        $actions['index']['prepareDataProvider'] = [$this, 'prepareDataProvider'];

        return $actions;
    }

    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'create' => ['POST'],
                    'delete' => ['DELETE'],
                    'update' => ['PUT', 'PATCH'],
                    '*' => ['GET'],
                ],
            ],
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'actions' => ['index', 'view', 'create', 'update', 'delete', 'statistics', 'hub-statistics'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
        ]);
    }

    public function prepareDataProvider()
    {
        $search = new OrderSearch();

        return $search->search(Yii::$app->getRequest()->getQueryParams());
    }

    /**
     * 订单统计
     *
     * @param null $organizationId 组织
     * @param null $platformId 平台
     * @param null $shopId 店铺
     * @param null $beginDate
     * @param null $endDate
     * @param string $index
     * @return array
     * @throws Exception
     */
    public function actionStatistics($organizationId = null, $platformId = null, $shopId = null, $beginDate = null, $endDate = null, $index = 'day')
    {
        $rawResults = [
            'shops' => [],
            'summary' => [
                'orders' => 0,
                'amount' => 0,
            ],
        ];
        $conditions = [];
        if ($shopId) {
            $conditions['id'] = explode(',', $shopId);
        } else {
            if ($organizationId) {
                $conditions['organization_id'] = explode(',', $organizationId);
            }
            if ($platformId) {
                $conditions['platform_id'] = explode(',', $platformId);
            }
        }
        $shops = (new Query())
            ->select(['id', 'name', 'organization_id', 'platform_id'])
            ->from(Shop::tableName())
            ->where($conditions)
            ->indexBy('id')
            ->all();
        if ($shops) {
            try {
                $beginDate = new DateTime($beginDate);
                $endDate = new DateTime($endDate);
                if ($beginDate > $endDate) {
                    $beginDate = new DateTime();
                    $endDate = new DateTime();
                }
            } catch (Exception $e) {
                $beginDate = new DateTime();
                $endDate = new DateTime();
            }

            $beginDate->setTime(0, 0, 0);
            $endDate->setTime(23, 59, 59);

            /* @var $formatter Formatter */
            $formatter = Yii::$app->getFormatter();
            $conditions = ['BETWEEN', 'place_order_at', $beginDate->getTimestamp(), $endDate->getTimestamp()];
            $conditions = ['AND', $conditions, ['shop_id' => array_keys($shops)]];
            $orders = (new Query())
                ->select(['shop_id', 'total_amount', 'place_order_at'])
                ->from(Order::tableName())
                ->where($conditions)
                ->orderBy(['place_order_at' => SORT_ASC]);
            foreach ($orders->batch() as $rows) {
                foreach ($rows as $row) {
                    switch ($index) {
                        case 'year':
                            $format = 'Y';
                            break;

                        case 'month':
                            $format = 'Y-m';
                            break;

                        default:
                            // Default is `day`
                            $format = 'Y-m-d';
                            break;
                    }
                    $key = date($format, $row['place_order_at']);
                    $amount = round($row['total_amount'], 2) * 100;
                    $rawResults['summary']['orders'] += 1;
                    $rawResults['summary']['amount'] += $amount;
                    $shopId = intval($row['shop_id']);
                    if (!isset($rawResults['shops'][$shopId])) {
                        $shop = $shops[$shopId] ?? null;
                        $rawResults['shops'][$shopId] = [
                            'organization_name' => $shop ? $formatter->asOrganization($shop['organization_id']) : null,
                            'platform_name' => $shop ? $formatter->asPlatform($shop['platform_id']) : null,
                            'shop_id' => $shopId,
                            'shop_name' => $shop ? $shop['name'] : null,
                            'orders' => 0,
                            'amount' => 0,
                            'items' => [],
                        ];
                    }

                    $rawResults['shops'][$shopId]['orders'] += 1;
                    $rawResults['shops'][$shopId]['amount'] += $amount;
                    if (!isset($rawResults['shops'][$shopId]['items'][$key])) {
                        $rawResults['shops'][$shopId]['items'][$key] = [
                            'orders' => 1,
                            'amount' => $amount,
                        ];
                    } else {
                        $rawResults['shops'][$shopId]['items'][$key]['orders'] += 1;
                        $rawResults['shops'][$shopId]['items'][$key]['amount'] += $amount;
                    }
                }
            }
        }

        $rawResults['summary']['amount'] /= 100;
        $shopsData = [];
        foreach ($rawResults['shops'] as &$shop) {
            $shop['amount'] /= 100;
            foreach ($shop['items'] as &$item) {
                $item['amount'] /= 100;
            }
            $shopsData[] = $shop;
        }
        unset($shop, $item);

        return [
            'shops' => $shopsData,
            'summary' => $rawResults['summary'],
        ];
    }

    /**
     * hub订单统计
     *
     * @param null $organizationId 组织
     * @param null $platformId 平台
     * @param null $shopId 店铺
     * @param null $beginDate
     * @param null $endDate
     * @param string $index
     * @return array
     * @throws Exception
     */
    public function actionHubStatistics($organizationId = null, $platformId = null, $shopId = null, $beginDate = null, $endDate = null, $index = 'day')
    {
        $rawResults = [
            'shops' => [],
            'summary' => [
                'total_price' => 0,
                'total_orders' => 0,
            ],
        ];
        $conditions = [];
        if ($shopId) {
            $conditions['id'] = explode(',', $shopId);
        } else {
            if ($organizationId) {
                $conditions['organization_id'] = explode(',', $organizationId);
            }
            if ($platformId) {
                $conditions['platform_id'] = explode(',', $platformId);
            }
        }
        $shops = (new Query())
            ->select(['id', 'name', 'organization_id', 'platform_id'])
            ->from(Shop::tableName())
            ->where($conditions)
            ->indexBy('id')
            ->all();
        if ($shops) {
            try {
                $beginDate = new DateTime($beginDate);
                $endDate = new DateTime($endDate);
                if ($beginDate > $endDate) {
                    $beginDate = new DateTime();
                    $endDate = new DateTime();
                }
            } catch (Exception $e) {
                $beginDate = new DateTime();
                $endDate = new DateTime();
            }

            $beginDate->setTime(0, 0, 0);
            $endDate->setTime(23, 59, 59);

            /* @var $formatter Formatter */
            $formatter = Yii::$app->getFormatter();
            $conditions = ['BETWEEN', 'place_order_at', $beginDate->getTimestamp(), $endDate->getTimestamp()];
            $conditions = ['AND', $conditions, ['shop_id' => array_keys($shops)]];
            $orders = (new Query())
                ->select(['shop_id', 'total_amount', 'place_order_at'])
                ->from(Order::tableName())
                ->where($conditions)
                ->orderBy(['place_order_at' => SORT_ASC]);
            switch ($index) {
                case 'year':
                    $format = 'Y';
                    break;

                case 'month':
                    $format = 'Y-m';
                    break;

                default:
                    // Default is `day`
                    $format = 'Y-m-d';
                    break;
            }
            $orders = $orders->all();
            if ($orders) {
                foreach ($orders as $row) {
                    $key = date($format, $row['place_order_at']);
                    $amount = round($row['total_amount'], 2) * 100;
                    $rawResults['summary']['total_orders'] += 1;
                    $rawResults['summary']['total_price'] += $amount;
                    $shopId = intval($row['shop_id']);
                    if (!isset($rawResults['shops'][$key])) {
                        $shop = $shops[$shopId] ?? null;
                        $rawResults['shops'][$key] = [
                            'project' => $formatter->asOrganization($shop['organization_id']) . "(" . $formatter->asPlatform($shop['platform_id']) . ")",
                            'date' => $key,
                            'total_orders' => 1,
                            'total_price' => $amount,

                        ];
                    } else {
                        $rawResults['shops'][$key]['total_orders'] += 1;
                        $rawResults['shops'][$key]['total_price'] += $amount;
                    }

                    $start = $beginDate->getTimestamp();
                    for ($start; $start <= $endDate->getTimestamp(); $start += 24 * 3600) {
                        $key = date("Y-m-d", $start);
                        if (!isset($rawResults['shops'][$key])) {
                            $rawResults['shops'][$key] = [
                                'project' => $formatter->asOrganization($shop['organization_id']) . "(" . $formatter->asPlatform($shop['platform_id']) . ")",
                                'date' => $key,
                                'total_orders' => 0,
                                'total_price' => 0,
                            ];
                        }
                    }
                }
            } else {
                foreach ($shops as $shop) {
                    $start = $beginDate->getTimestamp();
                    for ($start; $start <= $endDate->getTimestamp(); $start += 24 * 3600) {
                        $key = date("Y-m-d", $start);
                        $rawResults['shops'][$key] = [
                            'project' => $formatter->asOrganization($shop['organization_id']) . "(" . $formatter->asPlatform($shop['platform_id']) . ")",
                            'date' => $key,
                            'total_orders' => 0,
                            'total_price' => 0,
                        ];
                    }
                }
            }
        }
        $rawResults['summary']['total_price'] /= 100;
        $shopsData = [];
        foreach ($rawResults['shops'] as &$shop) {
            $shop['total_price'] /= 100;
            $shopsData[] = $shop;
        }
        unset($shop);
        sort($shopsData);

        return [
            'history' => $shopsData,
            'summary' => $rawResults['summary'],
        ];
    }

}

<?php

namespace app\modules\api\modules\g\controllers;

use app\modules\api\modules\g\extensions\Formatter;
use app\modules\api\modules\g\models\Package;
use app\modules\api\modules\g\models\PackageSearch;
use DateTime;
use Yii;
use yii\db\Query;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\BadRequestHttpException;
use yii\web\NotFoundHttpException;

/**
 * /api/g/package/
 * 公共包裹接口
 *
 * @package app\modules\api\modules\g\controllers
 */
class PackageController extends Controller
{

    public $modelClass = Package::class;

    public function actions()
    {
        $actions = parent::actions();
        unset($actions['create'], $actions['delete'], $actions['view']);
        $actions['index']['prepareDataProvider'] = [$this, 'prepareDataProvider'];

        return $actions;
    }

    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'update' => ['PUT', 'PATCH'],
                    '*' => ['GET'],
                ],
            ],
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'actions' => ['index', 'view', 'update', 'statistics'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
        ]);
    }

    /**
     * @return \yii\data\ActiveDataProvider
     * @throws \Exception
     */
    public function prepareDataProvider()
    {
        $search = new PackageSearch();

        return $search->search(Yii::$app->getRequest()->getQueryParams());
    }

    /**
     * 包裹详情
     *
     * @param null $id
     * @param string $type
     * @return array|\yii\db\ActiveRecord|null
     * @throws BadRequestHttpException
     * @throws NotFoundHttpException
     */
    public function actionView($id, $type = 'id')
    {
        $id = trim($id);
        if (empty($id)) {
            throw new BadRequestHttpException("请提供正确的 id 参数值。");
        }

        switch (strtolower($type)) {
            case 'id':
                $condition['id'] = intval($id);
                break;

            case 'number':
                $condition['number'] = trim($id);
                break;

            case 'waybill_number':
                $condition['waybill_number'] = trim($id);
                break;

            default:
                $condition = [];
                break;
        }
        if (!$condition) {
            throw new BadRequestHttpException("Type `$type` is invalid.");
        }
        $model = Package::find()->where($condition)->one();
        if ($model === null) {
            throw new NotFoundHttpException("包裹 $id 不存在。");
        }

        return $model;
    }

    /**
     * 统计
     *
     * @param null $begin_date
     * @param null $end_date
     * @param string $group_by
     * @return array
     * @throws \Exception
     */
    public function actionStatistics($begin_date = null, $end_date = null, $group_by = 'organization')
    {
        $items = [];
        try {
            $beginDate = new DateTime($begin_date);
            $endDate = new DateTime($end_date);
        } catch (\Exception $e) {
            $beginDate = new DateTime();
            $endDate = new DateTime();
        }
        $beginDate->setTime(0, 0, 0);
        $endDate->setTime(23, 59, 59);
        /* @var $formatter Formatter */
        $formatter = Yii::$app->getFormatter();
        $totalAmount = 0;
        $totalCount = 0;
        if ($group_by == 'organization') {
            // 根据组织
            $rows = (new Query())
                ->select(['IFNULL([[shop.organization_id]], 0) AS [[organization_id]]', 'SUM([[freight_cost]]) AS [[freight_cost]]', 'COUNT(*) AS [[count]]'])
                ->from('{{%g_package}} t')
                ->leftJoin('{{%g_shop}} shop', '[[t.shop_id]] = [[shop.id]]')
                ->where(['BETWEEN', 't.delivery_datetime', $beginDate->getTimestamp(), $endDate->getTimestamp()])
                ->groupBy(['shop.organization_id'])
                ->all();
            foreach ($rows as $row) {
                $totalCount += $row['count'];
                $totalAmount += $row['freight_cost'];
                $key = intval($row['organization_id']);
                $items[$key] = [
                    'id' => $key,
                    'name' => $formatter->asOrganization($key),
                    'count' => intval($row['count']),
                    'amount' => floatval($row['freight_cost']),
                    'percent' => '0%',
                ];
            }
        } elseif ($group_by == 'company') {
            // 根据物流公司
            $rows = (new Query())
                ->select(['wc.id', 'wc.name', 'SUM([[freight_cost]]) AS [[freight_cost]]', 'COUNT(*) AS [[count]]'])
                ->from('{{%g_package}} t')
                ->leftJoin('{{%wuliu_company_line}} wcl', '[[t.logistics_line_id]] = [[wcl.id]]')
                ->leftJoin('{{%wuliu_company}} wc', '[[wcl.company_id]] = [[wc.id]]')
                ->where(['BETWEEN', 't.delivery_datetime', $beginDate->getTimestamp(), $endDate->getTimestamp()])
                ->groupBy(['wc.id'])
                ->all();
            foreach ($rows as $row) {
                $totalCount += $row['count'];
                $totalAmount += $row['freight_cost'];
                $key = intval($row['id']);
                $items[$key] = [
                    'id' => $key,
                    'name' => $row['name'] ?: '未知',
                    'count' => intval($row['count']),
                    'amount' => floatval($row['freight_cost']),
                    'percent' => '0%',
                ];
            }
        } elseif ($group_by == 'delivery') {
            $items = [
                'total' => 0,
                'weight' => [
                    'pending' => 0,
                    'finished' => 0
                ],
                'delivery' => [
                    'pending' => 0,
                    'finished' => 0
                ]
            ];
            $condition = '';
            $params = [];
            // @todo 起止时间段应该从哪里拿
            if ($begin_date && $end_date) {
                $condition = '[[id]] IN (SELECT [[package_id]] FROM {{%g_package_order_item}} WHERE order_id IN (SELECT [[id]] FROM {{%g_order}} WHERE [[place_order_at]] BETWEEN :begin AND :end))';
                $params = [
                    ':begin' => $beginDate->getTimestamp(),
                    ':end' => $endDate->getTimestamp(),
                ];
            }
            // 称重统计
            $rows = (new Query())
                ->select(['IF(IFNULL([[weight_datetime]], 0) > 0, "finished", "pending") AS [[calc_status]]', 'COUNT(*) AS [[count]]'])
                ->from('{{%g_package}}')
                ->where($condition, $params)
                ->groupBy(['calc_status'])
                ->all();

            foreach ($rows as $row) {
                $c = intval($row['count']);
                $items['total'] += $c;
                switch ($row['calc_status']) {
                    case 'pending':
                        $items['weight']['pending'] += $c;
                        break;

                    case 'finished':
                        $items['weight']['finished'] += $c;
                        break;
                }
            }

            // 发货统计
            $rows = (new Query())
                ->select(['IF(IFNULL([[delivery_datetime]], 0) > 0, "finished", "pending") AS [[calc_status]]', 'COUNT(*) AS [[count]]'])
                ->from('{{%g_package}}')
                ->where($condition, $params)
                ->groupBy(['calc_status'])
                ->all();

            foreach ($rows as $row) {
                $c = intval($row['count']);
                $items['total'] += $c;
                switch ($row['calc_status']) {
                    case 'pending':
                        $items['delivery']['pending'] += $c;
                        break;

                    case 'finished':
                        $items['delivery']['finished'] += $c;
                        break;
                }
            }

            return $items;
        }
        // 加入未匹配数据
        $packageNotMatchCount = (new Query())
            ->from("{{%wuliu_package_not_match}}")
            ->where(['BETWEEN', 'scan_at', $beginDate->getTimestamp(), $endDate->getTimestamp()])
            ->count();
        if ($packageNotMatchCount) {
            $key = '-1';
            $items[$key] = [
                'id' => $key,
                'name' => '未匹配已发货',
                'count' => intval($packageNotMatchCount),
                'amount' => 0,
                'percent' => '0%',
            ];
            $totalCount += $packageNotMatchCount;
        }
        if ($items) {
            foreach ($items as &$item) {
                if ($item['count']) {
                    $item['percent'] = $formatter->asPercent(round($item['count'] / $totalCount, 4), 2);
                }
            }
            unset($item);
            $items = array_values($items);
        }

        return [
            'summary' => [
                'count' => $totalCount,
                'amount' => $totalAmount,
            ],
            'items' => $items,
        ];
    }

}

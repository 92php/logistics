<?php

namespace app\modules\api\modules\om\controllers;

use app\modules\api\modules\om\forms\BatchReceiptForm;
use app\modules\api\modules\om\models\OrderItemRoute;
use app\modules\api\modules\om\models\OrderItemRouteSearch;
use Exception;
use Yii;
use yii\base\InvalidConfigException;
use yii\data\ActiveDataProvider;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\ServerErrorHttpException;

/**
 * /api/om/order-item-route
 * 订单路由接口
 *
 * @package app\modules\api\modules\om\controllers
 */
class OrderItemRouteController extends Controller
{

    public $modelClass = OrderItemRoute::class;

    public function actions()
    {
        $actions = parent::actions();
        $actions['index']['prepareDataProvider'] = [$this, 'prepareDataProvider'];
        unset($actions['delete']);

        return $actions;
    }

    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'create' => ['POST'],
                    'batch-receipt' => ['POST'],
                    'delete' => ['DELETE'],
                    'update' => ['PUT', 'PATCH'],
                    '*' => ['GET'],
                ],
            ],
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'actions' => ['index', 'view', 'create', 'update', 'batch-receipt', 'inspection-statistics'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
        ]);
    }

    /**
     * @return ActiveDataProvider
     * @throws Exception
     */
    public function prepareDataProvider()
    {
        $search = new OrderItemRouteSearch();

        return $search->search(\Yii::$app->getRequest()->getQueryParams());
    }

    /**
     * 仓库批量收货
     *
     * @throws InvalidConfigException
     * @throws ServerErrorHttpException
     * @throws \Throwable
     */
    public function actionBatchReceipt()
    {
        $model = new BatchReceiptForm();
        $model->load(Yii::$app->getRequest()->getBodyParams(), '');
        if ($model->validate() && $model->save()) {
            Yii::$app->getResponse()->setStatusCode(201);
        } elseif (!$model->hasErrors()) {
            throw new ServerErrorHttpException('Failed to create the object for unknown reason.');
        }

        return $model;
    }

    /**
     * 获取质检列表
     *
     * @param null $waybill_number 运单号
     * @return array
     */
    public function actionInspectionStatistics($waybill_number = null)
    {
        $statistics = [
            'inspected' => 0,
            'noInspection' => 0,
            'all' => 0
        ];
        $query = OrderItemRoute::find()
            ->select([OrderItemRoute::tableName() . '.current_node', 'COUNT(*) AS total'])
            ->innerJoin("{{%om_package}} p", "p.id =" . OrderItemRoute::tableName() . '.package_id')
            ->innerJoin("{{%om_order_item_business}} ob", 'ob.order_item_id = ' . OrderItemRoute::tableName() . '.order_item_id')
            ->where(['IN', OrderItemRoute::tableName() . '.current_node', [OrderItemRoute::NODE_ALREADY_SHIPPED, OrderItemRoute::NODE_STAY_INSPECTION, OrderItemRoute::NODE_ALREADY_COMPLETE]]);

        if ($waybill_number) {
            $query->andWhere(['p.waybill_number' => $waybill_number]);
        }
        $query->groupBy([OrderItemRoute::tableName() . '.current_node']);
        $items = $query->asArray()->all();

        foreach ($items as $item) {
            $statistics['all'] += $item['total'];
            if (in_array($item['current_node'], [OrderItemRoute::NODE_ALREADY_SHIPPED, OrderItemRoute::NODE_STAY_INSPECTION])) {
                $statistics['inspected'] = $item['total'];
            } else {
                $statistics['noInspection'] = $item['total'];
            }
        }

        return $statistics;
    }
}

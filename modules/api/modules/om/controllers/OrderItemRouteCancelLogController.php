<?php

namespace app\modules\api\modules\om\controllers;

use app\modules\api\modules\om\models\OrderItemRouteCancelLog;
use app\modules\api\modules\om\models\OrderItemRouteCancelLogSearch;
use Yii;
use yii\base\InvalidConfigException;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\BadRequestHttpException;
use yii\web\ServerErrorHttpException;

/**
 * /api/om/order-item-route-cancel-log
 * 取消记录接口
 *
 * @package app\modules\api\modules\om\controllers
 */
class OrderItemRouteCancelLogController extends Controller
{

    public $modelClass = OrderItemRouteCancelLog::class;

    public function actions()
    {
        $actions = parent::actions();
        $actions['index']['prepareDataProvider'] = [$this, 'prepareDataProvider'];
        unset($actions['create'], $actions['update'], $actions['delete']);

        return $actions;
    }

    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'cancel' => ['POST'],
                    'update' => ['PUT', 'PATCH'],
                    '*' => ['GET'],
                ],
            ],
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'actions' => ['index', 'view', 'cancel', 'status-options', 'is-cancel'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
        ]);
    }

    public function prepareDataProvider()
    {
        $search = new OrderItemRouteCancelLogSearch();

        return $search->search(\Yii::$app->getRequest()->getQueryParams());
    }

    /**
     * 仓库申请取消
     *
     * @return OrderItemRouteCancelLog
     * @throws BadRequestHttpException
     * @throws ServerErrorHttpException
     * @throws InvalidConfigException
     */
    public function actionCancel()
    {
        $post = Yii::$app->request->getBodyParams();
        if (!isset($post['order_item_route_id']) || !isset($post['canceled_quantity'])) {
            throw new BadRequestHttpException("参数 order_item_route_id 和 canceled_quantity 是必须的");
        }
        $model = new OrderItemRouteCancelLog();
        $model->load([
            'order_item_route_id' => $post['order_item_route_id'],
            'canceled_quantity' => $post['canceled_quantity'],
            'canceled_reason' => isset($post['canceled_reason']) ? $post['canceled_reason'] : '',
            'type' => isset($post['type']) ? $post['type'] : '',
        ], '');
        if ($model->save()) {
            Yii::$app->getResponse()->setStatusCode(201);
        } elseif (!$model->hasErrors()) {
            throw new ServerErrorHttpException('Failed to create the object for unknown reason.');
        }

        return $model;
    }

    /**
     * 取消订单选项以及统计
     */
    public function actionStatusOptions()
    {
        // TODO 需要加入供应商
        $options = [];
        foreach (OrderItemRouteCancelLog::ConfirmStatusOption() as $key => $value) {
            $options[$key] = [
                'key' => $key,
                'name' => $value,
                'count' => 0,
            ];
        }
        $n = 0;
        if ($options) {
            $routes = Yii::$app->getDb()->createCommand("SELECT [[confirmed_status]], COUNT(*) AS [[count]] FROM {{%om_order_item_route_cancel_log}} rcl LEFT JOIN {{%om_order_item_route}} r ON r.id = rcl.order_item_route_id LEFT JOIN {{%g_vendor}} v ON v.id = r.vendor_id WHERE [[confirmed_status]] IN (" . implode(', ', array_keys($options)) . ") AND [[v.member_id]] = :memberId GROUP BY [[confirmed_status]]", [':memberId' => Yii::$app->getUser()->getId()])->query();
            foreach ($routes as $route) {
                if (isset($options[$route['confirmed_status']])) {
                    $n += $route['count'];
                    $options[$route['confirmed_status']]['count'] = $route['count'];
                }
            }
        }

        array_unshift($options, [
            'key' => "",
            'name' => '全部',
            'count' => $n
        ]);

        return array_values($options);
    }

    /**
     * 是否取消
     *
     * @param $orderItemId
     * @return bool
     * @throws \yii\db\Exception
     */
    public function actionIsCancel($orderItemId)
    {
        $isCancel = true;
        $db = Yii::$app->getDb();
        $cancel = $db->createCommand("SELECT * FROM {{%om_order_item_route}} r LEFT JOIN {{%om_order_item_route_cancel_log}} rcl ON rcl.order_item_route_id = r.id WHERE [[r.order_item_id]] = :orderItemId ORDER BY rcl.id desc", [':orderItemId' => $orderItemId])->queryOne();

        if (!is_null($cancel['confirmed_status']) && $cancel['confirmed_status'] == OrderItemRouteCancelLog::STATUS_STAY_CONFIRM) {
            $isCancel = false;
        }

        return $isCancel;
    }

}

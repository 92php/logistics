<?php

namespace app\modules\api\modules\om\controllers;

use app\modules\api\modules\om\forms\BatchWarehousingForm;
use app\modules\api\modules\om\forms\PlaceOrderBatchForm;
use app\modules\api\modules\om\forms\ProductCheckForm;
use app\modules\api\modules\om\forms\ProductIgnoreForm;
use app\modules\api\modules\om\forms\ProductInspectionForm;
use app\modules\api\modules\om\forms\UpdateCustomizedForm;
use app\modules\api\modules\om\models\OrderItem;
use app\modules\api\modules\om\models\OrderItemBusiness;
use app\modules\api\modules\om\models\OrderItemSearch;
use Yii;
use yii\base\InvalidConfigException;
use yii\db\Query;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use yii\web\ServerErrorHttpException;

/**
 * /api/om/order-item
 * 订单详情接口
 *
 * @package app\modules\api\modules\om\controllers
 */
class OrderItemController extends Controller
{

    public $modelClass = OrderItem::class;

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
                    'product-inspection' => ['POST'],
                    'update' => ['PUT', 'PATCH'],
                    '*' => ['GET'],
                    'place-order' => ['PUT', 'PATCH'],
                    'check' => ['PUT', 'PATCH'],
                    'update-customized' => ['PUT', 'PATCH'],
                    'batch-warehousing' => ['POST'],
                    'ignore' => ['PUT', 'PATCH']
                ],
            ],
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'actions' => ['index', 'view', 'create', 'update', 'relation-package', 'search-place-order', 'product-inspection', 'place-order', 'check', 'update-customized', 'batch-warehousing', 'ignore'],
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
        $search = new OrderItemSearch();

        return $search->search(Yii::$app->getRequest()->getQueryParams());
    }

    /**
     * 质检商品
     *
     * @return ProductInspectionForm
     * @throws InvalidConfigException
     * @throws ServerErrorHttpException
     * @throws \Throwable
     */
    public function actionProductInspection()
    {
        $model = new ProductInspectionForm();
        $model->load(Yii::$app->getRequest()->getBodyParams(), '');
        if ($model->validate() && $model->save()) {
            Yii::$app->getResponse()->setStatusCode(201);
        } elseif (!$model->hasErrors()) {
            throw new ServerErrorHttpException('Failed to create the object for unknown reason.');
        }

        return $model;
    }

    /**
     * 搜索下单商品
     *
     * @param string $ids
     * @param string $type
     * @return array
     */
    public function actionSearchPlaceOrder($ids, $type = 'order')
    {
        $ids = array_unique(array_filter(explode(',', $ids)));
        if (!$ids) {
            return [];
        }
        $items = [];

        // 获取产品
        $orderItems = (new Query())->select(['t.id', 't.sku', 't.product_name', 't.extend', 't.quantity'])
            ->from("{{%g_order_item}} t")
            ->leftJoin("{{%om_order_item_business}} ob", '[[t.id]] = [[ob.order_item_id]]')
            ->where('[[t.vendor_id]] = 0')
            ->andWhere(['IN', 'ob.status', [OrderItemBusiness::STATUS_STAY_PLACE_ORDER, OrderItemBusiness::STATUS_IN_HANDLE, OrderItemBusiness::STATUS_STAY_REJECT]]);

        if ($type == 'order') {
            $orderItems->andWhere(['IN', 't.order_id', $ids]);
        } else {
            $orderItems->andWhere(['IN', 't.id', $ids]);
        }
        foreach ($orderItems->all() as $orderItem) {
            $key = strtolower($orderItem['sku']);
            if (!isset($items[$key])) {
                $items[$key] = [
                    'sku' => $orderItem['sku'],
                    'product_name' => $orderItem['product_name'],
                    'quantity' => 0,
                    'order_items' => [],
                    'vendor' => [],
                ];
            }
            $items[$key]['order_items'][] = [
                'id' => (int) $orderItem['id'],
                'extend' => json_decode($orderItem['extend'], true),
                'quantity' => (int) $orderItem['quantity'],
            ];
            $items[$key]['quantity'] += $orderItem['quantity'];
        }
        if ($items) {
            // 获取 sku 和供应商
            $vendors = (new Query())
                ->select(['v.id', 'sv.sku', 'sv.cost_price', 'v.name'])
                ->from("{{%om_sku_vendor}} sv")
                ->leftJoin("{{%g_vendor}} v", 'v.id = sv.vendor_id')
                ->where(['sv.sku' => array_keys($items)])
                ->orderBy('[[ordering]] ASC')
                ->all();

            foreach ($vendors as $vendor) {
                $sku = strtolower($vendor['sku']);
                $items[$sku]['vendor'][] = [
                    'vendor_id' => (int) $vendor['id'],
                    'vendor_name' => $vendor['name'],
                    'cost_price' => (float) $vendor['cost_price']
                ];
            }
        }

        return array_values($items);
    }

    /**
     * 批量下单
     *
     * @throws ServerErrorHttpException
     * @throws InvalidConfigException
     * @throws \Throwable
     */
    public function actionPlaceOrder()
    {
        $model = new PlaceOrderBatchForm();
        $model->load(Yii::$app->getRequest()->getBodyParams(), '');
        if ($model->validate() && $model->save()) {
            Yii::$app->getResponse()->setStatusCode(201);
        } elseif (!$model->hasErrors()) {
            throw new ServerErrorHttpException('Failed to create the object for unknown reason.');
        }

        return $model;
    }

    /**
     * 核对商品
     *
     * @throws InvalidConfigException
     * @throws ServerErrorHttpException
     * @throws \Throwable
     */
    public function actionCheck()
    {
        $model = new ProductCheckForm();
        $model->load(Yii::$app->getRequest()->getBodyParams(), '');
        if ($model->validate() && $model->save()) {
            Yii::$app->getResponse()->setStatusCode(201);
        } elseif (!$model->hasErrors()) {
            throw new ServerErrorHttpException('Failed to create the object for unknown reason.');
        }

        return $model;
    }

    /**
     * 修改定制信息
     *
     * @throws InvalidConfigException
     * @throws ServerErrorHttpException
     * @throws \Throwable
     */
    public function actionUpdateCustomized()
    {
        $model = new UpdateCustomizedForm();
        $model->load(Yii::$app->getRequest()->getBodyParams(), '');
        if ($model->validate() && $model->save()) {
            Yii::$app->getResponse()->setStatusCode(201);
        } elseif (!$model->hasErrors()) {
            throw new ServerErrorHttpException('Failed to create the object for unknown reason.');
        }

        return $model;
    }

    /**
     * 商品批量入库
     *
     * @return BatchWarehousingForm
     * @throws InvalidConfigException
     * @throws ServerErrorHttpException
     * @throws \Throwable
     */
    public function actionBatchWarehousing()
    {
        $model = new BatchWarehousingForm();
        $model->load(Yii::$app->getRequest()->getBodyParams(), '');
        if ($model->validate() && $model->save()) {
            Yii::$app->getResponse()->setStatusCode(201);
        } elseif (!$model->hasErrors()) {
            throw new ServerErrorHttpException('Failed to create the object for unknown reason.');
        }

        return $model;
    }

    /**
     * 产品忽略
     *
     * @return ProductIgnoreForm
     * @throws InvalidConfigException
     * @throws ServerErrorHttpException
     */
    public function actionIgnore()
    {
        $model = new ProductIgnoreForm();
        $model->load(Yii::$app->getRequest()->getBodyParams(), '');
        if ($model->validate() && $model->save()) {
            Yii::$app->getResponse()->setStatusCode(201);
        } elseif (!$model->hasErrors()) {
            throw new ServerErrorHttpException('Failed to create the object for unknown reason.');
        }

        return $model;
    }

}

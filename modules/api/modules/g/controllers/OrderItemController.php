<?php

namespace app\modules\api\modules\g\controllers;

use app\modules\api\modules\g\models\OrderItem;
use app\modules\api\modules\g\models\OrderItemSearch;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;

/**
 * /api/order-item/
 * 公共订单详情数据
 *
 * @package app\modules\api\modules\g\controllers
 */
class OrderItemController extends Controller
{

    public $modelClass = OrderItem::class;

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
                        'actions' => ['index', 'view', 'create', 'update', 'delete'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
        ]);
    }

    public function prepareDataProvider()
    {
        $search = new OrderItemSearch();

        return $search->search(Yii::$app->getRequest()->getQueryParams());
    }

}

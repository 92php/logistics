<?php

namespace app\modules\api\modules\g\controllers;

use app\modules\api\modules\g\models\Shop;
use app\modules\api\modules\g\models\ShopSearch;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;

/**
 * /api/g/shop
 * 店铺接口
 *
 * @package app\modules\api\modules\g\controllers
 */
class ShopController extends Controller
{

    public $modelClass = Shop::class;

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
                        'actions' => ['index', 'view', 'create', 'update', 'delete', 'list'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
        ]);
    }

    public function prepareDataProvider()
    {
        $search = new ShopSearch();

        return $search->search(Yii::$app->getRequest()->getQueryParams());
    }

}

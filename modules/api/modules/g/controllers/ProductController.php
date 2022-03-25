<?php

namespace app\modules\api\modules\g\controllers;

use app\modules\api\modules\g\models\ProductSearch;
use app\modules\api\modules\g\models\Product;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;

/**
 * g/product 接口
 * 商品接口
 *
 * @package app\modules\api\modules\g\controllers
 */
class ProductController extends Controller
{

    public $modelClass = Product::class;

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
        $search = new ProductSearch();

        return $search->search(\Yii::$app->getRequest()->getQueryParams());
    }

}

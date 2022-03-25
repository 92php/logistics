<?php

namespace app\modules\api\modules\hub\modules\shopify\controllers;

use app\modules\api\modules\hub\models\Shop;
use app\modules\api\modules\hub\models\ShopSearch;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;

/**
 * hub/shop 接口
 *
 * @package app\modules\api\modules\hub\controllers
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

    /**
     * @return array
     */
    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'create' => ['POST'],
                    'update' => ['PUT', 'PATCH'],
                    'delete' => ['DELETE'],
                    '*' => ['GET'],
                ],
            ],
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'actions' => ['index', 'create', 'update', 'delete', 'view'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
        ]);
    }

    /**
     * @return \yii\data\ActiveDataProvider
     */
    public function prepareDataProvider()
    {
        $search = new ShopSearch();

        return $search->search(\Yii::$app->getRequest()->getQueryParams());
    }

}
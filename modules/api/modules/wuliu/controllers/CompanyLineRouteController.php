<?php

namespace app\modules\api\modules\wuliu\controllers;

use app\modules\api\modules\wuliu\models\CompanyLineRoute;
use app\modules\api\modules\wuliu\models\CompanyLineRouteSearch;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;

/**
 * Class CompanyLineRouteController
 *
 * 物流公司线路路由接口
 *
 * @package app\modules\api\modules\wuliu\controllers
 */
class CompanyLineRouteController extends Controller
{

    public $modelClass = CompanyLineRoute::class;

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
        $search = new CompanyLineRouteSearch();

        return $search->search(\Yii::$app->getRequest()->getQueryParams());
    }

}

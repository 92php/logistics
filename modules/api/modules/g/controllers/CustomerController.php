<?php

namespace app\modules\api\modules\g\controllers;

use app\modules\api\modules\g\models\Customer;
use app\modules\api\modules\g\models\CustomerSearch;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;

/**
 * api/g/customer 客户接口
 *
 * @package app\modules\api\modules\g\controllers
 */
class CustomerController extends Controller
{

    public $modelClass = Customer::class;

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
        $search = new CustomerSearch();

        return $search->search(Yii::$app->getRequest()->getQueryParams());
    }

}

<?php

namespace app\modules\api\modules\g\controllers;

use app\modules\api\modules\g\models\CustomerAddress;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;

/**
 * api/g/customer-address 客户地址接口
 *
 * @package app\modules\api\modules\g\controllers
 */
class CustomerAddressController extends Controller
{

    public $modelClass = CustomerAddress::class;

    public function actions()
    {
        $actions = parent::actions();
        unset($actions['index']);

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
                        'actions' => ['view', 'create', 'update', 'delete'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
        ]);
    }

}

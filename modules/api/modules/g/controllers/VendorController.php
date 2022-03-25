<?php

namespace app\modules\api\modules\g\controllers;

use app\modules\api\modules\g\models\Vendor;
use app\modules\api\modules\g\models\VendorSearch;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;

/**
 * /api/g/vendor
 * 供应商接口
 *
 * @package app\modules\api\modules\g\controllers
 */
class VendorController extends Controller
{

    public $modelClass = Vendor::class;

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
        $search = new VendorSearch();

        return $search->search(\Yii::$app->getRequest()->getQueryParams());
    }

}

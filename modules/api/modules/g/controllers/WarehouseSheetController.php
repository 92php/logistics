<?php

namespace app\modules\api\modules\g\controllers;

use app\modules\api\modules\g\models\Warehouse;
use app\modules\api\modules\g\models\WarehouseSearch;
use app\modules\api\modules\g\models\WarehouseSheet;
use app\modules\api\modules\g\models\WarehouseSheetSearch;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;

/**
 * /api/g/warehouseSheet
 * 出入库记录
 *
 * @package app\modules\api\modules\g\controllers
 */
class WarehouseSheetController extends Controller
{

    public $modelClass = WarehouseSheet::class;

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
                    'delete' => ['DELETE'],
                    '*' => ['GET'],
                ],
            ],
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'actions' => ['index', 'view', 'create', 'list'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
        ]);
    }

    public function prepareDataProvider()
    {
        $search = new WarehouseSheetSearch();

        return $search->search(Yii::$app->getRequest()->getQueryParams());
    }

}

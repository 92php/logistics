<?php

namespace app\modules\api\modules\g\controllers;

use Yii;
use app\modules\api\modules\g\models\Rack;
use app\modules\api\modules\g\models\RackSearch;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;

/**
 * api/g/rack 货架接口
 *
 * @package app\modules\api\modules\g\controllers
 */
class RackController extends Controller
{

    public $modelClass = Rack::class;

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
        $search = new RackSearch();

        return $search->search(Yii::$app->getRequest()->getQueryParams());
    }

}

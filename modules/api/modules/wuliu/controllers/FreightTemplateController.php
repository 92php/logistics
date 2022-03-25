<?php

namespace app\modules\api\modules\wuliu\controllers;

use app\modules\api\modules\wuliu\models\FreightTemplate;
use app\modules\api\modules\wuliu\models\FreightTemplateSearch;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;

/**
 * 物流模板接口
 *
 * @package app\modules\api\modules\wuliu\controllers
 */
class FreightTemplateController extends Controller
{

    public $modelClass = FreightTemplate::class;

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
        $search = new FreightTemplateSearch();

        return $search->search(\Yii::$app->getRequest()->getQueryParams());
    }

}

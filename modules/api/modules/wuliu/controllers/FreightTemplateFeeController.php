<?php

namespace app\modules\api\modules\wuliu\controllers;

use app\modules\api\modules\wuliu\models\FreightTemplateFee;
use app\modules\api\modules\wuliu\models\FreightTemplateFeeSearch;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;

/**
 * 物流模板费用接口
 *
 * @package app\modules\api\modules\wuliu\controllers
 */
class FreightTemplateFeeController extends Controller
{

    public $modelClass = FreightTemplateFee::class;

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
        $search = new FreightTemplateFeeSearch();

        return $search->search(\Yii::$app->getRequest()->getQueryParams());
    }

}

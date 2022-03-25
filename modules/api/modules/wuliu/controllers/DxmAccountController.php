<?php

namespace app\modules\api\modules\wuliu\controllers;

use app\modules\api\modules\wuliu\models\DxmAccount;
use app\modules\api\modules\wuliu\models\DxmAccountSearch;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;

/**
 * Class DxmAccountController
 *
 * 账号接口
 *
 * @package app\modules\api\modules\wuliu\controllers
 */
class DxmAccountController extends Controller
{

    public $modelClass = DxmAccount::class;

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
        $search = new DxmAccountSearch();

        return $search->search(\Yii::$app->getRequest()->getQueryParams());
    }

}

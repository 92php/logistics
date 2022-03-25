<?php

namespace app\modules\api\modules\wuliu\controllers;

use app\modules\api\modules\wuliu\models\Company;
use app\modules\api\modules\wuliu\models\CompanySearch;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;

/**
 * Class CompanyController
 *
 * 物流公司接口
 *
 * @package app\modules\api\modules\wuliu\controllers
 */
class CompanyController extends Controller
{

    public $modelClass = Company::class;

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
                        'actions' => ['index', 'list', 'view', 'create', 'update', 'delete'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
        ]);
    }

    public function prepareDataProvider()
    {
        $search = new CompanySearch();

        return $search->search(\Yii::$app->getRequest()->getQueryParams());
    }

}

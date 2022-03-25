<?php

namespace app\modules\api\modules\g\controllers;

use app\modules\api\modules\g\models\Country;
use app\modules\api\modules\g\models\CountrySearch;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;

/**
 * Class CountryController
 *
 * 国家接口
 *
 * @package app\modules\api\modules\g\controllers
 */
class CountryController extends Controller
{

    public $modelClass = Country::class;

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
        $search = new CountrySearch();

        return $search->search(\Yii::$app->getRequest()->getQueryParams());
    }

}

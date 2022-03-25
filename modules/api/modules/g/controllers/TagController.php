<?php

namespace app\modules\api\modules\g\controllers;

use app\modules\api\modules\g\models\Tag;
use app\modules\api\modules\g\models\TagSearch;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;

/**
 * 标签管理
 *
 * @package app\modules\admin\modules\g\controllers
 */
class TagController extends Controller
{

    public $modelClass = Tag::class;

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
        $search = new TagSearch();

        return $search->search(\Yii::$app->getRequest()->getQueryParams());
    }

}

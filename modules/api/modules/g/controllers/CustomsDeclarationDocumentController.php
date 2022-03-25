<?php

namespace app\modules\api\modules\g\controllers;

use app\modules\api\modules\g\models\CustomsDeclarationDocument;
use app\modules\api\modules\g\models\CustomsDeclarationDocumentSearch;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;

/**
 * 报关信息数据管理
 *
 * @package app\modules\admin\modules\g\controllers
 */
class CustomsDeclarationDocumentController extends Controller
{

    public $modelClass = CustomsDeclarationDocument::class;

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
        $search = new CustomsDeclarationDocumentSearch();

        return $search->search(\Yii::$app->getRequest()->getQueryParams());
    }

}

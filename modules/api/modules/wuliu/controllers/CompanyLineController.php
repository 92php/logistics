<?php

namespace app\modules\api\modules\wuliu\controllers;

use app\modules\api\modules\wuliu\forms\CompanyLineForm;
use app\modules\api\modules\wuliu\models\CompanyLine;
use app\modules\api\modules\wuliu\models\CompanyLineSearch;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\ServerErrorHttpException;

/**
 * Class CompanyLineController
 *
 * 物流公司线路接口
 *
 * @package app\modules\api\modules\wuliu\controllers
 */
class CompanyLineController extends Controller
{

    public $modelClass = CompanyLine::class;

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
                    'submit' => ['POST'],
                    'delete' => ['DELETE'],
                    'update' => ['PUT', 'PATCH'],
                    '*' => ['GET'],
                ],
            ],
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'actions' => ['index', 'list', 'view', 'create', 'update', 'delete', 'submit'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
        ]);
    }

    public function prepareDataProvider()
    {
        $search = new CompanyLineSearch();

        return $search->search(\Yii::$app->getRequest()->getQueryParams());
    }

    /**
     * 处理路由和运费信息表单
     *
     * @return CompanyLineForm
     * @throws ServerErrorHttpException
     * @throws \yii\base\InvalidConfigException
     * @throws \Throwable
     */
    public function actionSubmit()
    {
        $model = new CompanyLineForm();

        $model->load(Yii::$app->getRequest()->getBodyParams(), '');
        if ($model->save()) {
            Yii::$app->getResponse()->setStatusCode(201);
        } elseif (!$model->hasErrors()) {
            throw new ServerErrorHttpException('Failed to create the object for unknown reason.');
        }

        return $model;
    }

}

<?php

namespace app\modules\api\modules\wuliu\controllers;

use app\modules\api\modules\wuliu\forms\BatchChangePackageRoutePlanDatetimeForm;
use app\modules\api\modules\wuliu\forms\PackageRouteProcessForm;
use app\modules\api\modules\wuliu\models\Package;
use app\modules\api\modules\wuliu\models\PackageRoute;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\NotFoundHttpException;

/**
 * 包裹路由接口
 *
 * @package app\modules\api\modules\wuliu\controllers
 */
class PackageRouteController extends Controller
{

    public $modelClass = PackageRoute::class;

    public function actions()
    {
        return [];
    }

    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'process' => ['PUT', 'PATCH'],
                    'batch-change-plan-datetime' => ['POST'],
                ],
            ],
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'actions' => ['process', 'batch-change-plan-datetime'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
        ]);
    }

    /**
     * 包裹路由节点处理
     *
     * @param $id
     * @return mixed
     * @throws NotFoundHttpException
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\web\ServerErrorHttpException
     */
    public function actionProcess($id)
    {
        $model = PackageRoute::find()->where(['id' => (int) $id])->one();
        if ($model === null) {
            throw new NotFoundHttpException("包裹路由数据不存在。");
        }
        $form = new PackageRouteProcessForm();
        $form->model = $model;
        $form->load(Yii::$app->getRequest()->getBodyParams(), '');

        return $form->save();
    }

    /**
     * 批量修改包裹路由计划时间
     *
     * @param $packageId
     * @return BatchChangePackageRoutePlanDatetimeForm|bool
     * @throws NotFoundHttpException
     * @throws \yii\base\InvalidConfigException
     */
    public function actionBatchChangePlanDatetime($packageId)
    {
        /* @var $package Package */
        $package = Package::find()->where(['id' => (int) $packageId])->one();
        if ($package === null) {
            throw new NotFoundHttpException("包裹数据不存在。");
        }
        $form = new BatchChangePackageRoutePlanDatetimeForm();
        $form->package_id = $package->id;
        $form->load(Yii::$app->getRequest()->getBodyParams(), '');
        $form->validate();
        if ($form->save()) {
            Yii::$app->getResponse()->setStatusCode(201);
        }

        return $form;
    }

}

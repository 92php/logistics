<?php

namespace app\controllers;

use app\forms\PackageReportForm;
use Yii;
use yii\base\Model;
use yii\filters\VerbFilter;
use function var_dump;

/**
 * 物流包裹报表
 * Class PackageReportController
 *
 * @package app\controllers
 */
class PackageReportController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'upload' => ['GET', 'POST']
                ],
            ],
        ];
    }


    public function actionReport()
    {
        $model = new PackageReportForm();
        if ($model->load(Yii::$app->getRequest()->getBodyParams()) && $model->download()){
                return $this->redirect('index');
        }

        return $this->render('index', [
            'model' => $model
        ]);
    }
}
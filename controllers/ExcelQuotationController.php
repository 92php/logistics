<?php

namespace app\controllers;

use app\forms\QuotationExcelUploadForm;
use Yii;
use yii\filters\VerbFilter;
use yii\web\UploadedFile;

/**
 * Class ExcelQuotationController
 *
 * @package app\controllers
 */
class ExcelQuotationController extends Controller
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

    public function actionUpload()
    {
        $model = new QuotationExcelUploadForm();

        if (Yii::$app->getRequest()->getIsPost()) {
            $model->file = UploadedFile::getInstance($model, 'file');

            $model->upload();
        }

        return $this->render('upload', [
            'model' => $model
        ]);
    }
}
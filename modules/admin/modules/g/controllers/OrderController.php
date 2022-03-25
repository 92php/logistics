<?php

namespace app\modules\admin\modules\g\controllers;

use app\modules\admin\modules\g\models\Order;
use app\modules\admin\modules\g\models\OrderSearch;
use app\modules\admin\modules\g\models\OrderStatisticsSearch;
use Yii;
use yii\filters\AccessControl;
use yii\web\NotFoundHttpException;

/**
 * 订单管理
 *
 * @package app\modules\admin\modules\g\controllers

 */
class OrderController extends Controller
{

    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'actions' => ['index', 'view', 'create', 'update', 'statistics'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
        ];
    }

    /**
     * Lists all Order models.
     *
     * @return mixed
     * @throws \Exception
     */
    public function actionIndex()
    {
        $searchModel = new OrderSearch();
        $params = Yii::$app->getRequest()->queryParams;
        if (!isset($params['OrderSearch']['begin_date'])) {
            $params['OrderSearch']['begin_date'] = date('Y-m-d');
        }
        if (!isset($params['OrderSearch']['end_date'])) {
            $params['OrderSearch']['end_date'] = date('Y-m-d');
        }
        $dataProvider = $searchModel->search($params);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Order model.
     *
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * Creates a new Order model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     *
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Order();
        $model->loadDefaultValues();

        if ($model->load(Yii::$app->getRequest()->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing Order model.
     * If update is successful, the browser will be redirected to the 'view' page.
     *
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->getRequest()->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * 统计
     *
     * @return string
     * @throws \Exception
     */
    public function actionStatistics()
    {
        $searchModel = new OrderStatisticsSearch();
        $params = Yii::$app->getRequest()->queryParams;
        if (!isset($params['OrderStatisticsSearch']['begin_date']) || !isset($params['OrderStatisticsSearch']['end_date'])) {
            $params['OrderStatisticsSearch']['begin_date'] = $params['OrderStatisticsSearch']['end_date'] = date('Y-m-d');
        }
        $dataProvider = $searchModel->search($params);

        return $this->render('statistics', [
            'model' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Finds the Order model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     *
     * @param integer $id
     * @return Order the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Order::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }

}

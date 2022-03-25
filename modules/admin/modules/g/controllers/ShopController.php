<?php

namespace app\modules\admin\modules\g\controllers;

use app\forms\DynamicForm;
use app\models\Meta;
use app\modules\admin\modules\g\forms\AddSyncTaskForm;
use app\modules\admin\modules\g\models\Shop;
use app\modules\admin\modules\g\models\ShopSearch;
use Exception;
use Yii;
use yii\db\Query;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\helpers\Html;
use yii\web\HttpException;
use yii\web\NotFoundHttpException;
use yii\web\Response;

/**
 * 店铺管理
 *
 * @package app\modules\admin\modules\g\controllers

 */
class ShopController extends Controller
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
                        'actions' => ['index', 'view', 'create', 'update', 'delete', 'switch', 'add-sync-task'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['POST'],
                    'switch' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * Lists all Shop models.
     *
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new ShopSearch();
        $dataProvider = $searchModel->search(Yii::$app->getRequest()->queryParams);

        return $this->render('index', [
            'dataProvider' => $dataProvider,
            'searchModel' => $searchModel,
        ]);
    }

    /**
     * Displays a single Shop model.
     *
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        $model = $this->findModel($id);

        return $this->render('view', [
            'model' => $model,
            'metaItems' => Meta::getItems($model),
        ]);
    }

    /**
     * 添加店铺
     *
     * @return string|\yii\web\Response
     * @throws HttpException
     */
    public function actionCreate()
    {
        $model = new Shop();
        $model->loadDefaultValues();
        $dynamicModel = new DynamicForm(Meta::getItems($model));

        $payload = Yii::$app->getRequest()->post();
        if ($model->load($payload) && $model->validate() && (!$dynamicModel->attributes || ($dynamicModel->load($payload) && $dynamicModel->validate()))) {
            $transaction = Yii::$app->getDb()->beginTransaction();
            try {
                $model->save(false);
                $dynamicModel->attributes && Meta::saveValues($model, $dynamicModel, true);
                $transaction->commit();
            } catch (Exception $e) {
                $transaction->rollback();
                throw new HttpException(500, $e->getMessage());
            }

            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('create', [
            'model' => $model,
            'dynamicModel' => $dynamicModel,
        ]);
    }

    /**
     * Updates an existing Shop model.
     * If update is successful, the browser will be redirected to the 'view' page.
     *
     * @param integer $id
     * @return mixed
     * @throws HttpException
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        $dynamicModel = new DynamicForm(Meta::getItems($model));

        $payload = Yii::$app->getRequest()->post();
        if ($model->load($payload) && $model->validate() && (!$dynamicModel->attributes || ($dynamicModel->load($payload) && $dynamicModel->validate()))) {
            $transaction = Yii::$app->getDb()->beginTransaction();
            try {
                $model->save(false);
                $dynamicModel->attributes && Meta::saveValues($model, $dynamicModel, true);
                $transaction->commit();
            } catch (Exception $e) {
                $transaction->rollback();
                throw new HttpException(500, $e->getMessage());
            }

            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('update', [
            'model' => $model,
            'dynamicModel' => $dynamicModel,
        ]);
    }

    /**
     * Deletes an existing Shop model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     *
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    /**
     * 激活控制
     *
     * @return Response
     * @throws \yii\db\Exception
     * @throws \Throwable
     * @throws \yii\base\InvalidConfigException
     */
    public function actionSwitch()
    {
        $id = Yii::$app->getRequest()->post('id');
        $db = Yii::$app->getDb();
        $value = $db->createCommand('SELECT [[enabled]] FROM {{%g_shop}} WHERE [[id]] = :id', [':id' => (int) $id])->queryScalar();
        if ($value !== false) {
            $value = !$value;
            $now = time();
            $db->createCommand()->update('{{%g_shop}}', ['enabled' => $value, 'updated_at' => $now, 'updated_by' => Yii::$app->getUser()->getId()], '[[id]] = :id', [':id' => (int) $id])->execute();
            $responseData = [
                'success' => true,
                'data' => [
                    'value' => $value,
                    'updatedAt' => Yii::$app->getFormatter()->asDate($now),
                    'updatedBy' => Yii::$app->getUser()->getIdentity()->username,
                ],
            ];
        } else {
            $responseData = [
                'success' => false,
                'error' => [
                    'message' => '数据有误',
                ],
            ];
        }

        return new Response([
            'format' => Response::FORMAT_JSON,
            'data' => $responseData,
        ]);
    }

    /**
     * 批量添加同步任务
     *
     * @param $shopIds
     * @return AddSyncTaskForm|string|Response
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\db\Exception
     */
    public function actionAddSyncTask($shopIds)
    {
        $shopIds = explode(',', $shopIds);
        $model = new AddSyncTaskForm();
        $model->begin_date = date('Y-m-d');
        $model->end_date = date('Y-m-d');
        $model->priority = 10;
        $shops = $shopIds ? (new Query())
            ->select('name')
            ->from('{{%g_shop}}')
            ->where(['id' => $shopIds])
            ->indexBy('id')
            ->column() : [];
        $model->shop_ids = array_keys($shops);
        $model->load(Yii::$app->getRequest()->post());
        if (Yii::$app->getRequest()->getIsPost() && $model->validate() && $model->save()) {
            Yii::$app->getSession()->setFlash('notice', sprintf('店铺 [%s] 同步任务已经添加。', implode(', ', $shops)) . Html::a('查看任务', ['sync-task/index']));

            return $this->redirect(['shop/index']);
        }

        return $this->render('add-sync-task', [
            'model' => $model,
            'shops' => $shops,
        ]);
    }

    /**
     * Finds the Shop model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     *
     * @param integer $id
     * @return Shop the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Shop::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }

}

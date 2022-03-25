<?php

namespace app\modules\api\modules\om\controllers;

use app\extensions\data\ArrayDataProvider;
use app\modules\api\modules\om\forms\SkuVendorBatchCreateForm;
use app\modules\api\modules\om\forms\SkuVendorBatchUpdateForm;
use app\modules\api\modules\om\models\SkuVendor;
use app\modules\api\modules\om\models\SkuVendorSearch;
use Yii;
use yii\base\InvalidConfigException;
use yii\db\Exception;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\NotFoundHttpException;
use yii\web\ServerErrorHttpException;

/**
 * /api/om/sku-vendor
 * 供应商接口
 *
 * @package app\modules\api\modules\om\controllers
 */
class SkuVendorController extends Controller
{

    public $modelClass = SkuVendor::class;

    public function actions()
    {
        $actions = parent::actions();
        $actions['index']['prepareDataProvider'] = [$this, 'prepareDataProvider'];
        unset($actions['view']);

        return $actions;
    }

    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'create' => ['POST'],
                    'batch-create' => ['POST'],
                    'delete' => ['DELETE'],
                    'update' => ['PUT', 'PATCH'],
                    'batch-update' => ['PUT', 'PATCH'],
                    '*' => ['GET'],
                ],
            ],
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'actions' => ['index', 'view', 'create', 'batch-create', 'update', 'batch-update', 'delete', 'product-to-excel'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
        ]);
    }

    /**
     * @return ArrayDataProvider
     * @throws Exception
     */
    public function prepareDataProvider()
    {
        $search = new SkuVendorSearch();

        return $search->search(Yii::$app->getRequest()->getQueryParams());
    }

    /**
     * 批量添加
     *
     * @return SkuVendorBatchCreateForm
     * @throws ServerErrorHttpException
     * @throws \Throwable
     * @throws InvalidConfigException
     */
    public function actionBatchCreate()
    {
        $model = new SkuVendorBatchCreateForm();
        $model->load(Yii::$app->getRequest()->getBodyParams(), '');
        if ($model->validate() && $model->save()) {
            Yii::$app->getResponse()->setStatusCode(201);
        } elseif (!$model->hasErrors()) {
            throw new ServerErrorHttpException('Failed to create the object for unknown reason.');
        }

        return $model;
    }

    /**
     * 批量修改
     *
     * @return SkuVendorBatchUpdateForm
     * @throws ServerErrorHttpException
     * @throws \Throwable
     * @throws InvalidConfigException
     */
    public function actionBatchUpdate()
    {
        $model = new SkuVendorBatchUpdateForm();
        $model->load(Yii::$app->getRequest()->getBodyParams(), '');
        if ($model->validate() && $model->save()) {
            Yii::$app->getResponse()->setStatusCode(200);
        } elseif (!$model->hasErrors()) {
            throw new ServerErrorHttpException('Failed to create the object for unknown reason.');
        }

        return $model;
    }

    /**
     * 详情
     *
     * @param $sku
     * @return array
     * @throws NotFoundHttpException
     * @throws Exception
     */
    public function actionView($sku)
    {
        $data = null;
        $rawItems = Yii::$app->getDb()->createCommand("SELECT [[t.id]], [[t.ordering]], [[t.sku]], [[t.vendor_id]], [[t.cost_price]], [[t.production_min_days]], [[t.production_max_days]], [[t.enabled]], [[t.remark]], [[vendor.name]] AS [[vendor_name]] FROM {{%om_sku_vendor}} t LEFT JOIN {{%g_vendor}} vendor ON [[t.vendor_id]] = [[vendor.id]] WHERE [[t.sku]] = :sku ORDER BY [[ordering]] ASC", [
            ':sku' => trim($sku)
        ])->queryAll();
        foreach ($rawItems as $i => $item) {
            if ($i == 0) {
                $data = [
                    'sku' => $item['sku'],
                    'items' => []
                ];
            }
            $data['items'][] = [
                'id' => (int) $item['id'],
                'ordering' => (int) $item['ordering'],
                'vendor_id' => (int) $item['vendor_id'],
                'vendor_name' => $item['vendor_name'],
                'cost_price' => (float) $item['cost_price'],
                'production_min_days' => (int) $item['production_min_days'],
                'production_max_days' => (int) $item['production_max_days'],
                'enabled' => boolval($item['enabled']),
                'remark' => $item['remark'],
            ];
        }
        if ($data === null) {
            throw new NotFoundHttpException("Not found.");
        }

        return $data;
    }

}

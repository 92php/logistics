<?php

namespace app\modules\api\modules\om\controllers;

use app\modules\api\modules\om\models\Package;
use app\modules\api\modules\om\models\PackageSearch;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\BadRequestHttpException;

/**
 * /api/om/package
 * om包裹接口
 *
 * @package app\modules\api\modules\om\controllers
 */
class PackageController extends Controller
{

    public $modelClass = Package::class;

    /**
     * @param string $action
     * @param null $model
     * @param array $params
     * @throws BadRequestHttpException
     * @throws \yii\web\ForbiddenHttpException
     */
    public function checkAccess($action, $model = null, $params = [])
    {
        parent::checkAccess($action, $model, $params);

        if ($action == 'delete' && $model->status) {
            throw new BadRequestHttpException('已发货订单不可删除。');
        }
    }

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
                        'actions' => ['index', 'view', 'create', 'update', 'delete'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
        ]);
    }

    public function prepareDataProvider()
    {
        $search = new PackageSearch();

        return $search->search(\Yii::$app->getRequest()->getQueryParams());
    }

}

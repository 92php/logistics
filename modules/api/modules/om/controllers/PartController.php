<?php

namespace app\modules\api\modules\om\controllers;

use app\modules\api\models\Constant;
use app\modules\api\modules\om\forms\PartUpsertForm;
use app\modules\api\modules\om\models\OrderItemBusiness;
use app\modules\api\modules\om\models\OrderItemRoute;
use app\modules\api\modules\om\models\Part;
use app\modules\api\modules\om\models\PartSearch;
use Yii;
use yii\data\ArrayDataProvider;
use yii\db\Query;
use yii\db\StaleObjectException;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\ServerErrorHttpException;

/**
 * Class PartController
 * 配件控制器
 *
 * @package app\modules\api\modules\om\controllers
 */
class PartController extends Controller
{

    public $modelClass = Part::class;

    public function actions()
    {
        $actions = parent::actions();
        $actions['index']['prepareDataProvider'] = [$this, 'prepareDataProvider'];
        unset($actions['create'], $actions['views']);

        return $actions;
    }

    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'clear' => ['DELETE'],
                    '*' => ['GET'],
                    'upsert' => ['POST'],
                    'update' => ['PUT', 'PATCH'],
                    'delete' => ['DELETE']
                ],
            ],
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'actions' => ['index', 'delete', 'update', 'upsert', 'match', 'clear'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
        ]);
    }

    public function prepareDataProvider()
    {
        $search = new PartSearch();

        return $search->search(\Yii::$app->getRequest()->getQueryParams());
    }

    /**
     * 配件修改或添加
     *
     * @return PartUpsertForm
     * @throws ServerErrorHttpException
     * @throws \yii\base\InvalidConfigException
     */
    public function actionUpsert()
    {
        $model = new PartUpsertForm();
        $model->load(Yii::$app->getRequest()->getBodyParams(), '');
        if ($model->validate() && $model = $model->upsert()) {
            Yii::$app->getResponse()->setStatusCode(201);
        } elseif (!$model->hasErrors()) {
            throw new ServerErrorHttpException('Failed to create the object for unknown reason.');
        }

        return $model;
    }

    /**
     * 配件匹配商品
     *
     * @param int $page
     * @param int $pageSize
     * @return ArrayDataProvider
     * @throws \yii\db\Exception
     */
    public function actionMatch($page = 1, $pageSize = 20)
    {
        // @todo 需要增加供应商
        $db = Yii::$app->getDb();
        $cmd = $db->createCommand();
        // 查询当前供应商所有不为空，且没被匹配的配件
        $parts = $db->createCommand("SELECT [[p.id]],[[p.sn]],[[p.sku]],[[p.customized]], [[p.order_item_id]] FROM {{%om_part}} p LEFT JOIN {{%g_vendor}} v ON [[v.id]] = [[p.vendor_id]]   WHERE [[v.member_id]] = :memberId AND [[is_empty]] = :isEmpty ", [':memberId' => Yii::$app->getUser()->getId(), ":isEmpty" => Constant::BOOLEAN_FALSE])->queryAll();
        $sku = [];
        $items = [];
        foreach ($parts as $part) {
            $sku[] = $part['sku'];
        }
        if ($sku) {
            // 获取所有SKU匹配的商品并且为生产中状态,并且是当前供应商
            $products = (new Query())
                ->select(['oi.*', '[[o.number]]', '[[o.payment_at]]', '[[oir.id]] AS route_id'])
                ->from("{{%g_order_item}} oi")
                ->leftJoin("{{%om_order_item_business}} ob", 'ob.order_item_id = oi.id')
                ->leftJoin("{{%om_order_item_route}} oir", 'oir.order_item_id = oi.id')
                ->leftJoin("{{%g_order}} o", 'o.id = oi.order_id')
                ->leftJoin("{{%g_vendor}} v", 'v.id = oir.vendor_id')
                ->leftJoin("{{%g_vendor_member}} vm", 'vm.vendor_id = v.id')
                ->where(['ob.status' => OrderItemBusiness::STATUS_IN_HANDLE, 'oir.current_node' => OrderItemRoute::NODE_ALREADY_PRODUCED, 'vm.member_id' => Yii::$app->getUser()->getId()])
                ->andWhere(['IN', 'sku', $sku])
                ->orderBy('o.payment_at asc')->all();
            // 根据定制信息匹配
            foreach ($products as $product) {
                $match = [];
                $extend = json_decode($product['extend'], true);
                foreach ($extend['names'] as $name) {
                    foreach ($parts as $partKey => $part) {
                        if ($part['order_item_id'] == 0) {
                            if ($part['sku'] == $product['sku'] && $part['customized'] == $name) {
                                // 匹配定制信息和SKU
                                // 判断不重复匹配
                                if (!isset($match[$name])) {
                                    $match[$name] = [
                                        'id' => $part['id'],
                                        'sn' => $part['sn'],
                                        'name' => $name,
                                        'key' => $partKey
                                    ];
                                }
                            }
                        } else {
                            // 获取已经匹配但没处理的配件
                            if ($product['id'] == $part['order_item_id'] && $name == $part['customized']) {
                                if (!isset($product['part'][$name])) {
                                    $product['part'][$name] = [
                                        'id' => $part['id'],
                                        'sn' => $part['sn'],
                                        'name' => $name,
                                        'key' => $partKey
                                    ];
                                }
                            }
                        }
                    }
                }

                if (isset($product['part'])) {
                    if (count($extend['names']) == count($product['part'])) {
                        $product['extend'] = json_decode($product['extend'], true);
                        $product['part'] = array_values($product['part']);
                        $items[] = $product;
                    }
                } else {
                    if ($match) {
                        if (count($extend['names']) == count($match)) {
                            foreach ($match as $key => $item) {
                                //修改数据入库
                                $cmd->update("{{%om_part}}", ['order_item_id' => $product['id']], ['id' => $item['id']])->execute();
                                unset($parts[$item['key']]);
                                unset($match[$key]['key']);
                                $product['part'][] = $item;
                            }
                            $product['extend'] = json_decode($product['extend'], true);
                            $items[] = $product;
                        }
                    }
                }
            }
        }

        return new ArrayDataProvider([
            'allModels' => $items,
            'pagination' => [
                'page' => (int) $page - 1,
                'pageSize' => (int) $pageSize ?: 20,
            ]
        ]);
    }

    /**
     * 清除
     *
     * @throws \Throwable
     * @throws StaleObjectException
     */
    public function actionClear()
    {
        // 获取当前供应商
        $vendorId = Yii::$app->getDb()->createCommand("SELECT [[id]] FROM {{%g_vendor}} WHERE [[member_id]] = :memberId", [':memberId' => Yii::$app->getUser()->getId()])->queryScalar();
        $parts = Part::findAll(['vendor_id' => $vendorId]);
        foreach ($parts as $part) {
            $part->delete();
        }
    }
}
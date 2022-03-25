<?php

namespace app\modules\admin\modules\g\models;

use app\helpers\Config;
use app\models\Constant;
use app\modules\admin\modules\om\models\OrderItemBusiness;
use app\modules\admin\modules\om\models\OrderItemRoute;
use Yii;
use yii\db\Query;

/**
 * This is the model class for table "{{%g_order_item}}".
 *
 * @property int $id
 * @property int $order_id 订单id
 * @property string $image 图片
 * @property int $product_id 产品
 * @property string $key Key
 * @property string $sku sku
 * @property string $product_name 商品名
 * @property string|null $extend 扩展
 * @property bool $ignored 是否忽略
 * @property int $quantity 数量
 * @property int $vendor_id 供应商
 * @property float|null $sale_price 售价
 * @property float|null $cost_price 成本
 * @property string|null $remark 备注
 */
class OrderItem extends \yii\db\ActiveRecord
{

    const STEPS_PAYMENT = 0; // 付款
    const STEPS_PLACE_ORDER = 1; // 下单
    const STEPS_RECEIPT = 2; // 接单
    const STEPS_PRODUCTION = 3; // 供应商生产
    const STEPS_DELIVER = 4; // 供应商发货
    const STEPS_RECEIVING = 5; // 仓库收货
    const STEPS_INSPECTION = 6; // 质检核对信息
    const STEPS_WAREHOUSING = 7; // 入库

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%g_order_item}}';
    }

    public function transactions()
    {
        return [
            self::SCENARIO_DEFAULT => self::OP_ALL,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['order_id', 'product_name'], 'required'],
            [['order_id', 'product_id', 'quantity', 'vendor_id'], 'integer'],
            [['extend'], 'safe'],
            ['ignored', 'boolean'],
            ['ignored', 'default', 'value' => Constant::BOOLEAN_FALSE],
            [['sale_price'], 'number', 'min' => 0],
            [['cost_price'], 'number', 'min' => 0],
            [['remark'], 'string'],
            [['key', 'sku', 'remark'], 'trim'],
            [['key'], 'string', 'max' => 30],
            [['sku'], 'string', 'max' => 100],
            [['product_name'], 'string', 'max' => 100],
            ['image', 'string', 'max' => 200],
            ['vendor_id', 'default', 'value' => 0],
            ['vendor_id', 'exist',
                'targetClass' => Vendor::class,
                'targetAttribute' => 'id',
                "when" => function ($model) {
                    return $model->vendor_id != 0;
                }]
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => '编号',
            'order_id' => '订单',
            'image' => "图片",
            'product_id' => '产品',
            'key' => 'Key',
            'sku' => 'SKU',
            'product_name' => '产品名',
            'extend' => '扩展',
            'ignored' => '忽略',
            'quantity' => '数量',
            'vendor_id' => '供应商',
            'sale_price' => '售价',
            'cost_price' => '成本价',
            'remark' => '备注',
        ];
    }

    /**
     * @return array 供应商列表
     */
    public static function map()
    {
        return (new Query())
            ->select(['product_name'])
            ->from(self::tableName())
            ->indexBy('id')
            ->column();
    }

    /**
     * 供应商
     *
     * @return \yii\db\ActiveQuery
     */
    public function getVendor()
    {
        return $this->hasOne(Vendor::class, ['id' => 'vendor_id']);
    }

    /**
     * 所属订单
     *
     * @return \yii\db\ActiveQuery
     */
    public function getOrder()
    {
        return $this->hasOne(Order::class, ['id' => 'order_id']);
    }

    /**
     * business
     *
     * @return \yii\db\ActiveQuery
     */
    public function getBusiness()
    {
        return $this->hasOne(OrderItemBusiness::class, ['order_item_id' => 'id']);
    }

    /**
     * route
     *
     * @return \yii\db\ActiveQuery
     */
    public function getRoute()
    {
        return $this->hasMany(OrderItemRoute::class, ['order_item_id' => 'id'])->orderBy(['id' => SORT_DESC]);
    }

    /**
     * 路由工作流向
     *
     * @return array
     * @throws \yii\db\Exception
     */
    public function getWorkflow()
    {
        $items = [];
        $db = Yii::$app->getDb();
        // 获取订单
        $orders = $db->createCommand("SELECT r.*, [[o.payment_at]] FROM {{%g_order}} o LEFT JOIN {{%g_order_item}} oi ON [[oi.order_id]] = [[o.id]] LEFT JOIN {{%om_order_item_route}} r ON [[r.order_item_id]] = [[oi.id]] WHERE [[oi.id]] = :id ORDER BY r.id desc", [':id' => $this->id])->queryAll();
        if ($orders) {
            foreach ($orders as $order) {
                if (!isset($items[$order['id']])) {
                    // 下单时间
                    $items[$order['id']] = [
                        'is_reissue' => $order['is_reissue'] ? true : false, // 是否补单
                        'current_node' => self::STEPS_PAYMENT,
                        'cancel' => false,
                        'steps' => [
                            [
                                'datetime' => $order['payment_at'],
                                'overtime' => false,
                                'title' => '付款',
                                'reject' => false,
                                'cancel' => false,
                                'arrive_node' => true
                            ],
                            [
                                'datetime' => '',
                                'overtime' => false,
                                'title' => '下单',
                                'reject' => false,
                                'cancel' => false,
                                'arrive_node' => false
                            ],
                            [
                                'datetime' => '',
                                'overtime' => false,
                                'title' => '接单',
                                'reject' => false,
                                'cancel' => false,
                                'arrive_node' => false
                            ],
                            [
                                'datetime' => '',
                                'overtime' => false,
                                'title' => '生产',
                                'reject' => false,
                                'cancel' => false,
                                'arrive_node' => false
                            ],
                            [
                                'datetime' => '',
                                'overtime' => false,
                                'title' => '发货',
                                'reject' => false,
                                'cancel' => false,
                                'arrive_node' => false
                            ],
                            [
                                'datetime' => '',
                                'overtime' => false,
                                'title' => '收货',
                                'reject' => false,
                                'cancel' => false,
                                'arrive_node' => false
                            ],
                            [
                                'datetime' => '',
                                'overtime' => false,
                                'title' => '质检',
                                'reject' => false,
                                'cancel' => false,
                                'arrive_node' => false
                            ],
                            [
                                'datetime' => '',
                                'overtime' => false,
                                'title' => '完成',
                                'reject' => false,
                                'cancel' => false,
                                'arrive_node' => false
                            ],
                        ]

                    ];

                    // 如果此条路由已被取消，则不加入后续工作流
                    if ($order['current_node'] != OrderItemRoute::NODE_ALREADY_CANCEL) {
                        if ($order['place_order_at']) {
                            // 如果有下单时间
                            $items[$order['id']]['steps'][self::STEPS_PLACE_ORDER]['datetime'] = $order['place_order_at'];
                            $items[$order['id']]['steps'][self::STEPS_PLACE_ORDER]['arrive_node'] = true;
                            $items[$order['id']]['current_node'] = self::STEPS_PLACE_ORDER;
                        }
                        // 下单状态
                        if ($order['receipt_status']) {
                            // 如果拒接
                            $items[$order['id']]['steps'][self::STEPS_RECEIPT]['arrive_node'] = true;
                            $items[$order['id']]['current_node'] = self::STEPS_RECEIPT;
                            if ($order['receipt_status'] == OrderItemRoute::STATUS_PLACE_ORDER_REFUSE) {
                                $items[$order['id']]['steps'][self::STEPS_RECEIPT]['reject'] = true;
                            } else {
                                $items[$order['id']]['steps'][self::STEPS_RECEIPT]['datetime'] = $order['receipt_at'];
                                // 判断供应商接单时间是否超时,以小时
                                $receiptDuration = $db->createCommand("SELECT [[receipt_duration]] FROM {{%g_vendor}} WHERE [[id]] = :id", [':id' => $order['vendor_id']])->queryScalar();
                                // 接单时间 减去下单时间
                                $differTime = $order['receipt_at'] - $order['place_order_at'];
                                if (($differTime % 86400 / 3600) > $receiptDuration) {
                                    $items[$order['id']]['steps'][self::STEPS_RECEIPT]['overtime'] = true;
                                }
                            }
                        }
                        // 生产
                        if ($order['production_status']) {
                            $items[$order['id']]['steps'][self::STEPS_PRODUCTION]['datetime'] = $order['production_at'];
                            $items[$order['id']]['steps'][self::STEPS_PRODUCTION]['arrive_node'] = true;
                            $items[$order['id']]['current_node'] = self::STEPS_PRODUCTION;
                        }
                        // 发货
                        if ($order['delivery_status']) {
                            $items[$order['id']]['steps'][self::STEPS_DELIVER]['datetime'] = $order['vendor_deliver_at'];
                            $items[$order['id']]['steps'][self::STEPS_DELIVER]['arrive_node'] = true;
                            $items[$order['id']]['current_node'] = self::STEPS_DELIVER;
                        }
                        // 收货
                        if ($order['receiving_status']) {
                            $items[$order['id']]['steps'][self::STEPS_RECEIVING]['datetime'] = $order['receiving_at'];
                            $items[$order['id']]['steps'][self::STEPS_RECEIVING]['arrive_node'] = true;
                            $items[$order['id']]['current_node'] = self::STEPS_RECEIVING;
                        }
                        // 质检
                        if ($order['inspection_status']) {
                            $items[$order['id']]['steps'][self::STEPS_INSPECTION]['datetime'] = $order['inspection_at'];
                            $items[$order['id']]['steps'][self::STEPS_INSPECTION]['arrive_node'] = true;
                            $items[$order['id']]['current_node'] = self::STEPS_INSPECTION;
                        }
                        // 入库， 质检状态过了 就入库
                        if ($order['inspection_status']) {
                            $items[$order['id']]['steps'][self::STEPS_WAREHOUSING]['datetime'] = $order['warehousing_at'];
                            $items[$order['id']]['steps'][self::STEPS_WAREHOUSING]['arrive_node'] = true;
                            $items[$order['id']]['current_node'] = self::STEPS_WAREHOUSING;
                        }
                    } else {
                        $items[$order['id']]['cancel'] = true;
                    }
                }
            }
        }

        return array_values($items);
    }

    /**
     * @param bool $insert
     * @return bool
     * @throws \yii\db\Exception
     */
    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            $product = Yii::$app->getDb()->createCommand('SELECT [[id]], [[chinese_name]] FROM {{%g_product}} WHERE [[sku]] = :sku', [':sku' => $this->sku])->queryOne();
            if ($product) {
                $this->product_id = $product['id'];
                $this->product_name = $product['chinese_name'];
            }

            return true;
        } else {
            return false;
        }
    }

    /**
     * @param bool $insert
     * @param array $changedAttributes
     * @throws \yii\db\Exception
     */
    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);
        // 会员业务逻辑处理
        $business = Config::get('orderItem.business', []);
        foreach ($business as $class => $params) {
            if (class_exists($class)) {
                try {
                    call_user_func([new $class(), 'afterSave'], $this, $insert, $changedAttributes, $params);
                } catch (\Exception $e) {
                    Yii::error($class . ':' . $e->getMessage(), 'orderItemBusiness');
                }
            }
        }
        $db = Yii::$app->getDb();
        $n = $db->createCommand('SELECT COUNT(*) FROM {{%g_order_item}} WHERE [[order_id]] = :orderId', [':orderId' => $this->order_id])->queryScalar();
        $db->createCommand()->update('{{%g_order}}', ['quantity' => $n], ['id' => $this->order_id])->execute();
    }

    public function afterDelete()
    {
        parent::afterDelete();
        PackageOrderItem::deleteAll(['order_item_id' => $this->id]);
        $business = Config::get('orderItem.business', []);
        foreach ($business as $class => $params) {
            if (class_exists($class)) {
                try {
                    call_user_func([new $class(), 'afterDelete'], $this, $params);
                } catch (\Exception $e) {
                    Yii::error($class . ':' . $e->getMessage(), 'orderItemBusiness');
                }
            }
        }
    }

}

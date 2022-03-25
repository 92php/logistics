<?php

namespace app\modules\admin\modules\om\models;

use app\models\Member;
use app\modules\admin\modules\g\models\Order;
use app\modules\admin\modules\g\models\OrderItem;
use app\modules\api\modules\om\models\OrderBusiness;
use app\modules\api\modules\om\models\OrderItemBusiness;
use Yii;
use yii\db\Query;

/**
 * This is the model class for table "{{%om_order_item_route_cancel_log}}".
 *
 * @property int $id
 * @property int $order_item_route_id orderItemRoute Id
 * @property string $canceled_reason 取消原因
 * @property int $canceled_quantity 取消数量
 * @property int $type 取消类型
 * @property int $canceled_at 取消时间
 * @property int $canceled_by 取消人
 * @property int $confirmed_status 确认状态
 * @property string|null $confirmed_message 确认反馈消息
 * @property int|null $confirmed_at 确认时间
 * @property int|null $confirmed_by 确认人
 */
class OrderItemRouteCancelLog extends \yii\db\ActiveRecord
{

    const STATUS_STAY_CONFIRM = 0; // 待确认
    const STATUS_APPROVE = 1; // 同意取消
    const STATUS_REJECT = 2; // 拒绝取消

    const TYPE_ROUTE = 0; // 取消下单
    const TYPE_ITEM = 1; // 取消商品

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%om_order_item_route_cancel_log}}';
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
            [['order_item_route_id'], 'required'],
            [['order_item_route_id', 'canceled_quantity', 'type', 'canceled_at', 'canceled_by', 'confirmed_status', 'confirmed_at', 'confirmed_by'], 'integer'],
            ['confirmed_status', 'default', 'value' => self::STATUS_STAY_CONFIRM],
            ['type', 'default', 'value' => self::TYPE_ROUTE],
            ['confirmed_status', 'in', 'range' => array_keys(self::ConfirmStatusOption())],
            // 过滤route，已取消和拒接单不能申请取消
            ['order_item_route_id', 'exist', 'targetClass' => OrderItemRoute::class, 'targetAttribute' => ['order_item_route_id' => 'id'], 'filter' => function ($query) {
                /* @var $query Query */
                $query->andWhere(['receipt_status' => [
                    OrderItemRoute::STATUS_PLACE_ORDER_STAY, OrderItemRoute::STATUS_PLACE_ORDER_ALREADY
                ]])->andWhere(['not', ['status' => OrderItemRoute::STATUS_CANCELED]]);
            }
            ],
            ['canceled_reason', 'required', 'when' => function () {
                $status = Yii::$app->db->createCommand('SELECT [[receipt_status]] FROM {{%om_order_item_route}} WHERE [[id]] = :id', [':id' => $this->order_item_route_id])->queryScalar();
                if ($status !== false) {
                    return $status == OrderItemRoute::STATUS_PLACE_ORDER_ALREADY;
                } else {
                    return false;
                }
            }],
            // 拒绝取消时原因是必填的
            ['confirmed_message', 'required', 'when' => function () {
                return $this->confirmed_status == self::STATUS_REJECT;
            }],
            // @todo 测试验证规则，禁止重复申请取消
            ['order_item_route_id', 'unique', 'targetAttribute' => ['order_item_route_id', 'confirmed_status'], 'filter' => ['confirmed_status' => self::STATUS_STAY_CONFIRM], 'message' => '禁止重复申请取消'],
            [['confirmed_message', 'canceled_reason'], 'string'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'order_item_route_id' => '商品路由ID',
            'canceled_reason' => '取消原因',
            'canceled_quantity' => '取消数量',
            'type' => '取消类型',
            'canceled_at' => '取消时间',
            'canceled_by' => '取消人',
            'confirmed_status' => '确认状态',
            'confirmed_message' => '确认反馈消息',
            'confirmed_at' => '确认时间',
            'confirmed_by' => '确认人',
        ];
    }

    /**
     * 确认状态
     *
     * @return array
     */
    public static function ConfirmStatusOption()
    {
        return [
            self::STATUS_STAY_CONFIRM => '待确认',
            self::STATUS_APPROVE => '同意取消',
            self::STATUS_REJECT => '拒绝取消',
        ];
    }

    /**
     * 取消类型
     *
     * @return array
     */
    public static function CancelTypeOption()
    {
        return [
            self::TYPE_ITEM => '取消商品',
            self::TYPE_ROUTE => '取消下单',
        ];
    }

    /**
     * 所属路由
     *
     * @return \yii\db\ActiveQuery
     */
    public function getRoute()
    {
        return $this->hasOne(OrderItemRoute::class, ['id' => 'order_item_route_id']);
    }

    /**
     * 取消申请人
     *
     * @return \yii\db\ActiveQuery
     */
    public function getRequestMember()
    {
        return $this->hasOne(Member::class, ['id' => 'canceled_by']);
    }

    /**
     * 取消确认人
     *
     * @return \yii\db\ActiveQuery
     */
    public function getConfirmMember()
    {
        return $this->hasOne(Member::class, ['id' => 'confirmed_by']);
    }

    /**
     * @param bool $insert
     * @return bool
     * @throws \yii\db\Exception
     */
    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            if ($insert) {
                $this->canceled_by = Yii::$app->getUser()->getId();
                $this->canceled_at = time();
                $receipt_status = Yii::$app->db->createCommand('SELECT [[receipt_status]] FROM {{%om_order_item_route}} WHERE [[id]] = :id', [':id' => $this->order_item_route_id])->queryScalar();
                if ($receipt_status == OrderItemRoute::STATUS_PLACE_ORDER_STAY) {
                    // 如果是未接单状态，不需要确认直接确认通过
                    $this->confirmed_status = self::STATUS_APPROVE;
                    $this->confirmed_at = time();
                    $this->confirmed_by = 0;
                }
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
        $route = OrderItemRoute::findOne(['id' => $this->order_item_route_id]);
        $db = Yii::$app->getDb();
        $cmd = $db->createCommand();
        if ($this->confirmed_status == self::STATUS_APPROVE) {
            if ($this->type == self::TYPE_ROUTE) {
                // 同意取消路由，减掉取消数量，数量小于0则取消route
                $route->quantity -= $this->canceled_quantity;
                if ($route->quantity <= 0) {
                    // 修改route.status为已取消,item.vendor_id为0,item.business.status为待下单
                    $route->quantity = 0;
                    $route->status = OrderItemRoute::STATUS_CANCELED;
                    $route->current_node = OrderItemRoute::NODE_ALREADY_CANCEL;
                    $cmd->update(OrderItem::tableName(), ['vendor_id' => 0, 'cost_price' => 0], ['id' => $route->order_item_id])->execute();
                    $cmd->update(OrderItemBusiness::tableName(), ['status' => OrderItemBusiness::STATUS_STAY_PLACE_ORDER], ['order_item_id' => $route->order_item_id])->execute();
                }
                $route->save();
            } elseif ($this->type == self::TYPE_ITEM) {
                // 取消整个商品
                $route->status = OrderItemRoute::STATUS_CANCELED;
                $route->current_node = OrderItemRoute::NODE_ALREADY_CANCEL;
                $cmd->update(OrderItemBusiness::tableName(), ['status' => OrderItemBusiness::STATUS_ALREADY_CANCELED], ['order_item_id' => $route->order_item_id])->execute();
            }
        }
        $route->save();
    }

}

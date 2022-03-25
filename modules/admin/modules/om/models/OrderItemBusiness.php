<?php

namespace app\modules\admin\modules\om\models;

/**
 * This is the model class for table "{{%om_order_business}}".
 *
 * @property int $id
 * @property int $order_item_id 订单详情id
 * @property int $priority 优先级
 * @property int|null $status 状态
 */
class OrderItemBusiness extends \yii\db\ActiveRecord
{

    /**
     * 优先级
     */
    const PRIORITY_NORMAL = 0;
    const PRIORITY_PRECEDENCE = 1;
    const PRIORITY_URGENT = 2;
    const PRIORITY_PRECEDENCE_URGENT = 3;

    /**
     * 状态选项
     */
    const STATUS_STAY_CHECK = 0; // 待核实
    const STATUS_STAY_PLACE_ORDER = 1; // 待下单
    const STATUS_IN_HANDLE = 2; // 处理中
    const STATUS_ALREADY_CANCELED = 3; // 已取消
    const STATUS_STAY_COMPLETE = 4; // 已完成
    const STATUS_STAY_REJECT = 5; // 拒接

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%om_order_item_business}}';
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
            [['order_item_id'], 'required'],
            [['order_item_id', 'priority', 'status'], 'integer'],
            ['priority', 'in', 'range' => array_keys(self::priorityOptions())],
            ['status', 'default', 'value' => self::STATUS_STAY_CHECK],
            ['status', 'in', 'range' => array_keys(self::statusOptions())],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'order_item_id' => '订单详情id',
            'priority' => '优先级',
            'status' => '状态',
        ];
    }

    /**
     * 优先级状态
     *
     * @return array
     */
    public static function priorityOptions()
    {
        return [
            self::PRIORITY_NORMAL => '',
            self::PRIORITY_PRECEDENCE => '优先',
            self::PRIORITY_URGENT => '加急',
            self::PRIORITY_PRECEDENCE_URGENT => '优先且加急',
        ];
    }

    /**
     * 状态选项
     *
     * @return array
     */
    public static function statusOptions()
    {
        return [
            self::STATUS_STAY_CHECK => '待核实',
            self::STATUS_STAY_PLACE_ORDER => '待下单',
            self::STATUS_IN_HANDLE => '处理中',
            self::STATUS_ALREADY_CANCELED => '已取消',
            self::STATUS_STAY_COMPLETE => '已完成',
            self::STATUS_STAY_REJECT => "拒接单"
        ];
    }

}

<?php

namespace app\modules\admin\modules\om\models;

use app\models\Member;
use app\modules\admin\modules\g\models\OrderItem;
use app\modules\admin\modules\g\models\Vendor;
use app\modules\api\models\Constant;
use Yii;
use function mt_rand;
use function time;

/**
 * This is the model class for table "{{%om_order_item_route}}".
 *
 * @property int $id
 * @property string|null $waybill_number 运单号
 * @property int|null $package_id 包裹
 * @property int|null $parent_id 上级
 * @property int $order_item_id 订单详情id
 * @property int $place_order_at 下单时间
 * @property int $place_order_by 下单人
 * @property int $vendor_id 供应商
 * @property int|null $receipt_at 接单时间
 * @property int $receipt_status 接单状态
 * @property int|null $production_at 生产时间
 * @property int $production_status 生产状态
 * @property int|null $vendor_deliver_at 供应商发货时间
 * @property int|null $delivery_status 发货状态
 * @property int|null $receiving_at 收货时间
 * @property int|null $receiving_status 收货状态
 * @property int|null $inspection_at 质检时间
 * @property int $inspection_by 质检人
 * @property int $inspection_status 质检状态
 * @property string $inspection_image 质检图片
 * @property int|null $warehousing_at 入库时间
 * @property int|null $inspection_number 质检数量
 * @property int|null $is_reissue 是否补发
 * @property int $quantity 数量
 * @property int status 状态
 * @property string|null $reason 拒接原因
 * @property string|null $feedback 反馈
 * @property string $feedback_image 反馈图片
 * @property string $information_image 信息匹配图片
 * @property string|null $information_feedback 信息匹配反馈
 * @property int|null $is_accord_with 是否符合质检标准
 * @property int|null $is_information_match 是否信息匹配
 * @property float $cost_price 成本价
 * @property int $current_node 当前节点
 * @property boolean is_print 是否打印
 * @property boolean is_export 是否导出
 */
class OrderItemRoute extends \yii\db\ActiveRecord
{

    /**
     *  接单状态
     */
    const STATUS_PLACE_ORDER_STAY = 0; // 待接单
    const STATUS_PLACE_ORDER_ALREADY = 1; // 已接单
    const STATUS_PLACE_ORDER_REFUSE = 2; // 拒接单

    const STATUS_DEFAULT = 0; // 默认状态
    const STATUS_CANCELED = 1; // 已取消

    /**
     *  发货状态
     */
    const STATUS_DELIVERY_FALSE = 0; // 未发货
    const STATUS_DELIVERY_TRUE = 1; // 已发货

    /**
     *  收货状态
     */
    const STATUS_RECEIVING_FALSE = 0; // 未收货
    const STATUS_RECEIVING_TRUE = 1; // 已收货

    /**
     *  生产状态
     */
    const STATUS_PRODUCED_FALSE = 0; // 待生产
    const STATUS_PRODUCED_TRUE = 1; // 已生产

    /**
     *  质检状态
     */
    const STATUS_STAY_INSPECTION = 0; // 待质检
    const STATUS_INSPECTION_SUCCESS = 1; // 质检

    /**
     *  节点状态(供应商|管理员)
     */
    const NODE_STAY_RECEIPT = 2; // 待接单|已下单
    const NODE_REJECT_ORDER = 3; // 拒接
    const NODE_TO_PRODUCED = 4; // 待生产|待生产
    const NODE_ALREADY_PRODUCED = 5; // 生产中|生产中
    const NODE_STAY_SHIPPED = 6; // 待发货|待发货
    const NODE_ALREADY_SHIPPED = 7; // 发货|待收货
    const NODE_STAY_INSPECTION = 8; // N|已收货/待质检
    const NODE_ALREADY_COMPLETE = 9; // 完成
    const NODE_ALREADY_CANCEL = 10; // 已取消

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%om_order_item_route}}';
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
            [['order_item_id', 'vendor_id', 'receipt_status'], 'required'],
            [['order_item_id', 'place_order_at', 'place_order_by', 'vendor_id', 'receipt_at', 'receipt_status', 'production_at', 'vendor_deliver_at', 'receiving_at', 'inspection_at', 'warehousing_at', 'inspection_number', 'is_reissue', 'is_accord_with', 'is_information_match', 'quantity', 'status', 'package_id', 'delivery_status', 'receiving_status', 'production_status', 'current_node', 'parent_id', 'inspection_by'], 'integer'],
            [[ 'is_print', 'is_export'], 'boolean'],
            ['cost_price', 'number', 'min' => 0.01],
            [['delivery_status'], 'default', 'value' => self::STATUS_DELIVERY_FALSE],
            [['receiving_status'], 'default', 'value' => self::STATUS_RECEIVING_FALSE],
            [['inspection_status'], 'default', 'value' => self::STATUS_STAY_INSPECTION],
            ['package_id', 'exist', 'targetClass' => Package::class, 'targetAttribute' => ['package_id' => 'id'], 'when' => function ($model) {
                return $model->package_id != 0;
            }],
            [['reason', 'feedback', 'information_feedback', 'feedback_image', 'information_image'], 'string'],
            [['parent_id', 'package_id', 'is_print', 'is_export'], 'default', 'value' => Constant::BOOLEAN_FALSE],
            ['current_node', 'default', 'value' => self::NODE_STAY_RECEIPT]
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'parent_id' => '上级',
            'package_id' => '包裹',
            'order_item_id' => '商品详情id',
            'place_order_at' => '下单时间',
            'place_order_by' => '下单人',
            'vendor_id' => '供应商id',
            'receipt_at' => '接单时间',
            'receipt_status' => '接单状态',
            'production_at' => '生产时间',
            'production_status' => '生产状态',
            'vendor_deliver_at' => '供应商发货时间',
            'delivery_status' => '发货状态',
            'receiving_at' => '收货时间',
            'receiving_status' => '收货时间',
            'inspection_at' => '质检时间',
            'inspection_status' => '质检状态',
            'inspection_by' => '质检人',
            'warehousing_at' => '入库时间',
            'inspection_number' => '已质检数量',
            'is_reissue' => '是否补发',
            'quantity' => '数量',
            'status' => '是否有效',
            'reason' => '原因',
            'feedback' => '反馈',
            'feedback_image' => '反馈图片',
            'information_feedback' => '信息反馈',
            'information_image' => '信息反馈图片',
            'is_accord_with' => '是否符合质检标准',
            'is_information_match' => '是否信息匹配',
            'cost_price' => '成本价',
            'current_node' => '当前节点',
            'is_print' => '是否打印',
            'is_export' => '是否导出'
        ];
    }

    /**
     * 包裹
     *
     * @return \yii\db\ActiveQuery
     */
    public function getPackage()
    {
        return $this->hasOne(Package::class, ['id' => 'package_id']);
    }

    /**
     * 商品
     *
     * @return \yii\db\ActiveQuery
     */
    public function getOrderItem()
    {
        return $this->hasOne(OrderItem::class, ['id' => 'order_item_id']);
    }

    /**
     * 包裹
     *
     * @return \yii\db\ActiveQuery
     */
    public function getVendor()
    {
        return $this->hasOne(Vendor::class, ['id' => 'vendor_id']);
    }

    /**
     * 包裹
     *
     * @return \yii\db\ActiveQuery
     */
    public function getPlaceOrderMember()
    {
        return $this->hasOne(Member::class, ['id' => 'place_order_by']);
    }

    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            if ($insert) {
                $this->place_order_by = Yii::$app->getUser()->getId();
                $this->place_order_at = time();
            }

            return true;
        } else {
            return false;
        }
    }

    /**
     * 状态
     *
     * @return array
     */
    public static function statusOptions()
    {
        return [
            self::NODE_STAY_RECEIPT => '待接单',
            self::NODE_REJECT_ORDER => '拒接单',
            self::NODE_TO_PRODUCED => '待生产',
            self::NODE_ALREADY_PRODUCED => '生产中',
            self::NODE_STAY_SHIPPED => '待发货',
            self::NODE_ALREADY_SHIPPED => '待收货',
            self::NODE_STAY_INSPECTION => '待质检',
            self::NODE_ALREADY_COMPLETE => '已完成',
            self::NODE_ALREADY_CANCEL => '已取消',
        ];
    }

    /**
     * 接单状态
     *
     * @return array
     */
    public static function PlaceOrderStatusOption()
    {
        return [
            self::STATUS_PLACE_ORDER_STAY => '待接单',
            self::STATUS_PLACE_ORDER_ALREADY => '已接单',
            self::STATUS_PLACE_ORDER_REFUSE => '拒接单',
        ];
    }

}

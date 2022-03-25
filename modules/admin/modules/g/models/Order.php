<?php

namespace app\modules\admin\modules\g\models;

use app\helpers\Config;
use app\models\Constant;
use app\models\Option;
use yadjet\helpers\IsHelper;
use Yii;

/**
 * This is the model class for table "{{%g_order}}".
 *
 * @property int $id
 * @property string $key 外部单号
 * @property string $number 订单号
 * @property int $type 订单类型
 * @property string $consignee_name 收件人姓名
 * @property string $consignee_mobile_phone 收件人电话
 * @property string $consignee_tel 收件人手机
 * @property int $country_id 收件人国家
 * @property string $consignee_state 收件人省/洲
 * @property string $consignee_city 收件人城市
 * @property string $consignee_address1 收件人地址1
 * @property string $consignee_address2 收件人地址2
 * @property string $consignee_postcode 收件人邮编
 * @property int $quantity 商品数量
 * @property float|null $total_amount 订单总金额
 * @property int $third_party_platform_id 第三方平台
 * @property int $third_party_platform_status 第三方平台状态
 * @property int $status 状态
 * @property int $platform_id 平台
 * @property int $shop_id 店铺
 * @property int $product_type 商品类型
 * @property int $place_order_at 下单时间
 * @property int|null $payment_at 付款时间
 * @property int|null $cancelled_at 取消时间
 * @property int|null $cancel_reason 取消原因
 * @property int|null $closed_at 关闭时间
 * @property string|null $remark 备注
 * @property int $created_at 添加时间
 * @property int $created_by 添加人
 * @property int $updated_at 更新时间
 * @property int $updated_by 更新人
 */
class Order extends \yii\db\ActiveRecord
{

    /**
     * 订单类型
     */
    const TYPE_NORMAL = 0; // 正常
    const TYPE_REISSUE = 1; // 补单

    /**
     * 订单状态
     */
    const STATUS_PENDING = 0; // 待处理
    const STATUS_INVALID = 1; // 无效
    const STATUS_IN_PRODUCTION = 2; // 生产中
    const STATUS_IN_PROGRESS = 3; // 处理中
    const STATUS_FAILURE = 4; // 失败
    const STATUS_FINISHED = 5; // 完成

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%g_order}}';
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
            [['type', 'country_id', 'quantity', 'status', 'third_party_platform_id', 'platform_id', 'shop_id', 'product_type', 'place_order_at', 'payment_at', 'cancelled_at', 'closed_at', 'created_at', 'created_by', 'updated_at', 'updated_by'], 'integer'],
            [['number', 'place_order_at'], 'required'],
            ['type', 'default', 'value' => self::TYPE_NORMAL],
            ['type', 'in', 'range' => array_keys(self::typeOptions())],
            ['country_id', 'default', 'value' => 0],
            ['quantity', 'default', 'value' => 0],
            [['key', 'consignee_postcode', 'consignee_name', 'consignee_city', 'number', 'consignee_mobile_phone', 'consignee_tel', 'consignee_state', 'consignee_address1', 'consignee_address2', 'cancel_reason', 'remark'], 'trim'],
            ['key', 'string', 'max' => 30],
            [['total_amount'], 'number'],
            [['remark'], 'string'],
            [['consignee_postcode'], 'string', 'max' => 20],
            [['consignee_state', 'number'], 'string', 'max' => 50],
            [['consignee_name', 'consignee_city'], 'string', 'max' => 60],
            [['consignee_mobile_phone', 'consignee_tel'], 'string', 'max' => 30],
            [['consignee_address1', 'consignee_address2'], 'string', 'max' => 200],
            ['third_party_platform_status', 'default', 'value' => 0],
            ['third_party_platform_id', 'in', 'range' => array_keys(Option::thirdPartyPlatforms())],
            ['platform_id', 'in', 'range' => array_keys(Option::platforms())],
            ['third_party_platform_status', 'in', 'range' => array_keys(Option::thirdPartyPlatformDxmOrderStatusOptions()), 'when' => function ($model) {
                return $model->third_party_platform_id == Constant::THIRD_PARTY_PLATFORM_DIAN_XIAO_MI;
            }],
            ['third_party_platform_status', 'in', 'range' => array_keys(Option::thirdPartyPlatformTongToolOrderStatusOptions()), 'when' => function ($model) {
                return $model->third_party_platform_id == Constant::THIRD_PARTY_PLATFORM_TONG_TOOL;
            }],
            ['status', 'default', 'value' => self::STATUS_PENDING],
            ['status', 'in', 'range' => array_keys(self::statusOptions())],
            ['product_type', 'default', 'value' => Shop::PRODUCT_TYPE_UNKNOWN],
            ['product_type', 'in', 'range' => array_keys(Shop::productTypeOptions())],
            ['cancel_reason', 'string'],
            ['number', 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => '编号',
            'key' => '外部单号',
            'number' => '订单号',
            'type' => '类型',
            'consignee_name' => '收件人姓名',
            'consignee_mobile_phone' => '收件人手机',
            'consignee_tel' => '收件人电话',
            'country_id' => '收件人国家',
            'country.chinese_name' => '收件人国家',
            'consignee_state' => '收件人省/洲',
            'consignee_city' => '收件人城市',
            'consignee_address1' => '收件人地址1',
            'consignee_address2' => '收件人地址2',
            'consignee_postcode' => '收件人邮编',
            'quantity' => '商品数量',
            'total_amount' => '总金额',
            'third_party_platform_id' => '第三方平台',
            'third_party_platform_status' => '第三方平台状态',
            'status' => '状态',
            'platform_id' => '所属平台',
            'shop_id' => '店铺',
            'product_type' => '商品类型',
            'place_order_at' => '下单时间',
            'payment_at' => '付款时间',
            'cancelled_at' => '取消时间',
            'cancel_reason' => '取消原因',
            'closed_at' => '关闭时间',
            'remark' => '备注',
            'created_at' => '添加时间',
            'created_by' => '添加人',
            'updated_at' => '修改时间',
            'updated_by' => '修改人',
        ];
    }

    /**
     * 订单类型选项
     *
     * @return array
     */
    public static function typeOptions()
    {
        return [
            self::TYPE_NORMAL => '正常',
            self::TYPE_REISSUE => '补单',
        ];
    }

    /**
     * 状态选项
     *
     * @return string[]
     */
    public static function statusOptions()
    {
        return [
            self::STATUS_PENDING => '待处理',
            self::STATUS_INVALID => '无效',
            self::STATUS_IN_PRODUCTION => '生产中',
            self::STATUS_IN_PROGRESS => '处理中',
            self::STATUS_FAILURE => '失败',
            self::STATUS_FINISHED => '完成',
        ];
    }

    /**
     * 订单详情
     *
     * @return \yii\db\ActiveQuery
     */
    public function getItems()
    {
        return $this->hasMany(OrderItem::class, ['order_id' => 'id']);
    }

    /**
     * 所属店铺
     *
     * @return \yii\db\ActiveQuery
     */
    public function getShop()
    {
        return $this->hasOne(Shop::class, ['id' => 'shop_id']);
    }

    /**
     * 所属国家
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCountry()
    {
        return $this->hasOne(Country::class, ['id' => 'country_id']);
    }

    /**
     * @param bool $insert
     * @return bool
     * @throws \yii\db\Exception
     */
    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            $userId = IsHelper::cli() ? 0 : (Yii::$app->getUser()->getId() ?: 0);
            if ($insert) {
                $this->created_at = $this->updated_at = time();
                $this->created_by = $this->updated_by = $userId;
            } else {
                $this->updated_at = time();
                $this->updated_by = $userId;
            }
            if ($this->shop_id) {
                $productType = Yii::$app->getDb()->createCommand('SELECT [[product_type]] FROM {{%g_shop}} WHERE [[id]] = :id', [':id' => $this->shop_id])->queryScalar();
            } else {
                $productType = Shop::PRODUCT_TYPE_UNKNOWN;
            }
            $this->product_type = $productType;

            if ($this->third_party_platform_id != Constant::THIRD_PARTY_PLATFORM_SHOPIFY) {
                // 从其他第三方 ERP 系统过来的需要对状态进行转换
                $status = self::STATUS_PENDING;
                foreach (Config::get("order.statusMap.{$this->third_party_platform_id}", []) as $systemStatus => $platformStatus) {
                    if ($platformStatus && in_array($this->third_party_platform_status, $platformStatus)) {
                        $status = $systemStatus;
                        break;
                    }
                }
                $this->status = $status;
            }

            return true;
        } else {
            return false;
        }
    }

    /**
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function afterDelete()
    {
        parent::afterDelete();
        $models = OrderItem::findAll(['order_id' => $this->id]);
        foreach ($models as $model) {
            $model->delete();
        }

        $db = Yii::$app->getDb();
        $cmd = $db->createCommand();
        $deletePackageOrderItemIds = [];
        $packageIds = [];
        $packageOrderItems = $db->createCommand('SELECT [[id]], [[package_id]] FROM {{%g_package_order_item}} WHERE [[order_id]] = :orderId', [':orderId' => $this->id])->queryAll();
        foreach ($packageOrderItems as $item) {
            $deletePackageOrderItemIds[] = $item['id'];
            $packageIds[] = $item['package_id'];
        }
        $cmd->delete('{{%g_package_order_item}}', ['id' => $deletePackageOrderItemIds])->execute();
        $packageIds = array_unique($packageIds);
        if ($packageIds) {
            $n = $db->createCommand('SELECT COUNT(*) FROM {{%g_package_order_item}} WHERE [[package_id]] IN (' . implode(',', $packageIds) . ')')->queryScalar();
            if ($n !== false && $n == 0) {
                // 包裹详情不存在，则删除包裹本身
                $cmd->delete('{{%g_package}}', ['id' => $packageIds])->execute();
            }
        }
    }

}

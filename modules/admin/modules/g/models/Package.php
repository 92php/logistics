<?php

namespace app\modules\admin\modules\g\models;

use app\helpers\Config;
use app\models\Constant;
use app\models\Option;
use DateTime;
use yadjet\helpers\IsHelper;
use Yii;
use yii\db\Exception;

/**
 * This is the model class for table "{{%g_package}}".
 *
 * @property int $id
 * @property string|null $key 包裹 Key
 * @property string $number 包裹号
 * @property int $country_id 国家
 * @property string|null $waybill_number 运单号
 * @property int $weight 重量
 * @property int $weight_datetime 称重时间
 * @property int $reference_weight 参考重量
 * @property int $reference_freight_cost 参考运费
 * @property float $freight_cost 运费
 * @property int|null $delivery_datetime 发货时间
 * @property int $logistics_line_id 物流线路
 * @property string|null $logistics_query_raw_results 物流查询结果
 * @property int|null $logistics_last_check_datetime 最后检测时间
 * @property int $estimate_days 预计天数
 * @property int $final_days 最终天数
 * @property int $sync_status 同步状态
 * @property int $shop_id 店铺
 * @property int $third_party_platform_id 第三方平台
 * @property int $third_party_platform_status 第三方平台状态
 * @property int $status 状态
 * @property string|null $remark 备注
 * @property int|null $created_at 创建时间
 * @property int|null $created_by 创建人
 * @property int|null $updated_at 修改时间
 * @property int|null $updated_by 修改人
 */
class Package extends \yii\db\ActiveRecord
{

    /**
     * 同步状态
     */
    const SYNC_NONE = 0;
    const SYNC_PENDING = 1;
    const SYNC_SUCCESSFUL = 2;

    /**
     * 状态选项
     */
    const STATUS_PENDING = 0; // 待处理
    const STATUS_RECEIVED = 1; // 已接单
    const STATUS_IN_TRANSIT = 2; // 运输途中
    const STATUS_WAITING_RECEIVE = 3; // 到达待取
    const STATUS_SUCCESSFUL_RECEIPTED = 4; // 成功签收
    const STATUS_NOT_FOUND = 5; // 查询不到
    const STATUS_DEFERRED = 6; // 运输过久
    const STATUS_MAYBE_ABNORMAL = 7; // 可能异常
    const STATUS_DELIVERY_FAILURE = 8; // 投递失败
    const STATUS_CLOSED = 9; // 关闭

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%g_package}}';
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
            [['number'], 'required'],
            [['country_id', 'weight', 'delivery_datetime', 'logistics_line_id', 'logistics_last_check_datetime', 'estimate_days', 'final_days', 'sync_status', 'shop_id', 'third_party_platform_id', 'third_party_platform_status', 'status', 'created_at', 'created_by', 'updated_at', 'updated_by', 'weight_datetime', 'reference_weight'], 'integer'],
            [['weight', 'logistics_line_id', 'reference_weight'], 'default', 'value' => 0],
            [['country_id'], 'default', 'value' => 0],
            [['freight_cost', 'reference_freight_cost'], 'default', 'value' => 0],
            [['freight_cost', 'reference_freight_cost'], 'number'],
            [['key', 'number', 'waybill_number', 'logistics_query_raw_results'], 'trim'],
            [['logistics_query_raw_results', 'remark'], 'string'],
            [['key', 'number'], 'string', 'max' => 30],
            [['waybill_number'], 'string', 'max' => 40],
            ['sync_status', 'default', 'value' => self::SYNC_NONE],
            ['sync_status', 'in', 'range' => array_keys(self::syncStatusOptions())],
            ['third_party_platform_id', 'in', 'range' => array_keys(Option::thirdPartyPlatforms())],
            ['third_party_platform_status', 'in', 'range' => array_keys(Option::thirdPartyPlatformDxmPackageStatusOptions()), 'when' => function ($model) {
                return $model->third_party_platform_id == Constant::THIRD_PARTY_PLATFORM_DIAN_XIAO_MI;
            }],
            ['third_party_platform_status', 'in', 'range' => array_keys(Option::thirdPartyPlatformTongToolPackageStatusOptions()), 'when' => function ($model) {
                return $model->third_party_platform_id == Constant::THIRD_PARTY_PLATFORM_TONG_TOOL;
            }],
            ['status', 'default', 'value' => self::STATUS_PENDING],
            ['status', 'in', 'range' => array_keys(self::statusOptions())],
            [['number'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => '编号',
            'key' => '包裹 Key',
            'number' => '包裹号',
            'country_id' => '国家',
            'country.abbreviation' => '国家',
            'waybill_number' => '运单号',
            'weight' => '重量',
            'weight_datetime' => '称重时间',
            'reference_weight' => '参考重量',
            'reference_freight_cost' => '参考运费',
            'freight_cost' => '运费',
            'delivery_datetime' => '发货时间',
            'logistics_line_id' => '物流线路',
            'logistics_query_raw_results' => '物流查询结果',
            'logistics_last_check_datetime' => '最后检测时间',
            'estimate_days' => '预计天数',
            'final_days' => '最终天数',
            'sync_status' => '同步状态',
            'shop_id' => '店铺',
            'third_party_platform_id' => '第三方平台',
            'third_party_platform_status' => '第三方平台状态',
            'status' => '状态',
            'remark' => '备注',
            'created_at' => '创建时间',
            'created_by' => '创建人',
            'updated_at' => '修改时间',
            'updated_by' => '修改人',
        ];
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
     * 所属店铺
     *
     * @return \yii\db\ActiveQuery
     */
    public function getShop()
    {
        return $this->hasOne(Shop::class, ['id' => 'shop_id']);
    }

    /**
     * 同步选项
     *
     * @return string[]
     */
    public static function syncStatusOptions()
    {
        return [
            self::SYNC_NONE => '未知',
            self::SYNC_PENDING => '待同步',
            self::SYNC_SUCCESSFUL => '已同步',
        ];
    }

    /**
     * 生成包裹号
     *
     * @return string
     * @throws Exception
     */
    public static function generateNumber()
    {
        $datetime = new DateTime();
        $number = $datetime->format('YmdHis');
        $n = Yii::$app->getDb()->createCommand('SELECT COUNT(*) FROM {{%g_package}} WHERE [[created_at]] BETWEEN :begin AND :end', [
            ':begin' => $datetime->setTime(0, 0, 0)->getTimestamp(),
            ':end' => $datetime->setTime(23, 23, 59)->getTimestamp(),
        ])->queryScalar();
        $n || $n = 1;

        return sprintf("P%s%06d%d", $number, $n, mt_rand(1000, 9999));
    }

    /**
     * 状态选项
     *
     * @return array
     */
    public static function statusOptions()
    {
        return [
            self::STATUS_PENDING => '待处理',
            self::STATUS_RECEIVED => '已接单',
            self::STATUS_IN_TRANSIT => '运输途中',
            self::STATUS_WAITING_RECEIVE => '到达待取',
            self::STATUS_SUCCESSFUL_RECEIPTED => '成功签收',
            self::STATUS_NOT_FOUND => '查询不到',
            self::STATUS_DEFERRED => '运输过久',
            self::STATUS_MAYBE_ABNORMAL => '可能异常',
            self::STATUS_DELIVERY_FAILURE => '投递失败',
        ];
    }

    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            $userId = IsHelper::cli() ? 0 : (Yii::$app->getUser()->getIsGuest() ? 0 : Yii::$app->getUser()->getId());
            if ($insert) {
                $status = self::STATUS_PENDING;
                foreach (Config::get("package.statusMap.{$this->third_party_platform_id}", []) as $systemStatus => $platformStatus) {
                    if ($platformStatus && in_array($this->third_party_platform_status, $platformStatus)) {
                        $status = $systemStatus;
                        break;
                    }
                }
                $this->status = $status;

                $this->created_at = $this->updated_at = time();
                $this->created_by = $this->updated_by = $userId;
            } else {
                $this->updated_at = time();
                $this->updated_by = $userId;
            }

            return true;
        } else {
            return false;
        }
    }

    /**
     * @param bool $insert
     * @param array $changedAttributes
     * @throws Exception
     */
    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);
        if ($this->waybill_number) {
            Yii::$app->getDb()->createCommand()->delete("{{%wuliu_package_not_match}}", ['number' => $this->waybill_number])->execute();
        }
    }

    public function afterDelete()
    {
        parent::afterDelete();
        PackageOrderItem::deleteAll(['package_id' => $this->id]);
    }

}

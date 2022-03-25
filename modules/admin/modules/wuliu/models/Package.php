<?php

namespace app\modules\admin\modules\wuliu\models;

use yadjet\helpers\IsHelper;
use Yii;

/**
 * This is the model class for table "{{%wuliu_package}}".
 *
 * @property int $id
 * @property int $package_id 包裹编号
 * @property string $package_number 包裹号
 * @property string $order_number 订单号
 * @property int $line_id 线路
 * @property string $waybill_number 运单号
 * @property int $country_id 收件国家
 * @property int $weight 重量
 * @property float $freight_cost 运费
 * @property int $dxm_account_id 店小秘帐号
 * @property string $shop_name 店铺名称
 * @property int $delivery_datetime 发货时间
 * @property int $estimate_days 预计天数
 * @property int $final_days 最终天数
 * @property string|null $logistics_query_raw_results 物流查询结果
 * @property int $last_check_datetime 最后检测时间
 * @property int $sync_status 同步状态
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

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%wuliu_package}}';
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
            [['package_id', 'package_number', 'order_number', 'line_id', 'waybill_number', 'dxm_account_id', 'shop_name'], 'required'],
            [['package_number', 'order_number', 'waybill_number', 'shop_name'], 'trim'],
            [['package_id', 'line_id', 'country_id', 'weight', 'dxm_account_id', 'delivery_datetime', 'estimate_days', 'final_days', 'last_check_datetime', 'status', 'created_at', 'created_by', 'updated_at', 'updated_by'], 'integer'],
            [['weight', 'freight_cost'], 'default', 'value' => 0],
            ['sync_status', 'default', 'value' => self::SYNC_NONE],
            ['sync_status', 'in', 'range' => array_keys(self::syncStatusOptions())],
            ['status', 'default', 'value' => self::STATUS_PENDING],
            ['status', 'in', 'range' => array_keys(self::statusOptions())],
            [['estimate_days', 'final_days'], 'default', 'value' => 0],
            [['freight_cost'], 'number'],
            [['logistics_query_raw_results', 'remark'], 'string'],
            [['package_number', 'waybill_number', 'shop_name'], 'string', 'max' => 30],
            [['order_number'], 'string', 'max' => 100],
            [['package_number'], 'unique'],
            [['waybill_number'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => '编号',
            'package_id' => '包裹编号',
            'package_number' => '包裹号',
            'order_number' => '订单号',
            'line_id' => '线路',
            'waybill_number' => '运单号',
            'country_id' => '收件国家',
            'country.chinese_name' => '收件国家',
            'weight' => '重量',
            'freight_cost' => '运费',
            'dxm_account_id' => '店小秘帐号',
            'dxmAccount.username' => '店小秘帐号',
            'shop_name' => '店铺名称',
            'delivery_datetime' => '发货时间',
            'estimate_days' => '预计天数',
            'final_days' => '最终天数',
            'logistics_query_raw_results' => '物流查询结果',
            'last_check_datetime' => '最后检测时间',
            'sync_status' => '同步状态',
            'status' => '状态',
            'remark' => '备注',
            'created_at' => '创建时间',
            'created_by' => '创建人',
            'updated_at' => '修改时间',
            'updated_by' => '修改人',
        ];
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

    /**
     * 路由
     *
     * @return array|\yii\db\ActiveRecord[]
     */
    public function getRoutes()
    {
        return PackageRoute::find()
            ->alias('t')
            ->select('t.*')
            ->leftJoin('{{%wuliu_company_line_route}} r', '[[t.line_route_id]] = [[r.id]]')
            ->orderBy(['r.step' => SORT_ASC])
            ->where(['t.package_id' => $this->id])
            ->all();
    }

    /**
     * 收件国家
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCountry()
    {
        return $this->hasOne(Country::class, ['id' => 'country_id']);
    }

    /**
     * 所属线路
     *
     * @return \yii\db\ActiveQuery
     */
    public function getLine()
    {
        return $this->hasOne(CompanyLine::class, ['id' => 'line_id']);
    }

    /**
     * 计算运费
     *
     * @param $packageId
     * @return null
     * @throws \yii\db\Exception
     */
    public static function calcFreightCost($packageId)
    {
        $value = 0;
        $db = Yii::$app->getDb();
        $config = $db->createCommand('
SELECT [[t.weight]], [[template.id]], [[template.min_weight]], [[template.max_weight]], [[template.first_weight]], [[template.first_fee]], [[template.continued_weight]], [[template.continued_fee]], [[template.base_fee]]
FROM {{%wuliu_package}} [[t]]
LEFT JOIN {{%wuliu_freight_template_fee}} [[template]] ON [[t.line_id]] = [[template.line_id]]
WHERE [[t.id]] = :id ORDER BY [[template.min_weight]] ASC
', [':id' => (int) $packageId])->queryOne();
        if ($config && $config['id'] && $config['weight']) {
            $value = $config['base_fee'];
            $weight = $config['weight'];
            $maxWeight = $config['max_weight'];
            // 计算每单位的费用
            $fnCalcFee = function ($fee, $weight) {
                return round($fee / $weight, 5);
            };
            $feePerUnit = $fnCalcFee($config['first_fee'], $config['first_weight']);
            if ($weight <= $maxWeight) {
                $value += $feePerUnit * $weight;
            } else {
                $value += $feePerUnit * $maxWeight; // 首重费用
                $value += ($weight - $maxWeight) * $fnCalcFee($config['continued_fee'], $config['continued_weight']); // 首重费用 + 续重费用
            }
        }

        return $value;
    }

    /**
     * 运费估算
     *
     * @return float|int|mixed
     * @throws \yii\db\Exception
     */
    public function getFreightCostEstimate()
    {
        $value = 0;
        if ($this->line_id && $this->weight) {
            $data = Yii::$app->getDb()->createCommand('
SELECT [[t.min_weight]], [[t.max_weight]], [[t.first_weight]], [[t.first_fee]], [[t.continued_weight]], [[t.continued_fee]], [[t.base_fee]]
FROM {{%wuliu_freight_template_fee}} [[t]]
WHERE [[t.line_id]] = :lineId ORDER BY [[t.min_weight]] ASC
', [':lineId' => $this->line_id])->queryOne();
            if ($data) {
                $value = $data['base_fee'];
                $weight = $this->weight;
                $maxWeight = $data['max_weight'];
                // 计算每单位的费用
                $fnCalcFee = function ($fee, $weight) {
                    return round($fee / $weight, 5);
                };
                $feePerUnit = $fnCalcFee($data['first_fee'], $data['first_weight']);
                if ($weight <= $maxWeight) {
                    $value += $feePerUnit * $weight;
                } else {
                    $value += $feePerUnit * $maxWeight; // 首重费用
                    $value += ($weight - $maxWeight) * $fnCalcFee($data['continued_fee'], $data['continued_weight']); // 首重费用 + 续重费用
                }
            }
        }

        return $value;
    }

    /**
     * 店小秘账户
     *
     * @return \yii\db\ActiveQuery
     */
    public function getDxmAccount()
    {
        return $this->hasOne(DxmAccount::class, ['id' => 'dxm_account_id']);
    }

    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            $userId = IsHelper::cli() ? 0 : Yii::$app->getUser()->getId();
            if ($insert) {
                $this->delivery_datetime = null;
                $this->status = self::STATUS_PENDING;
                $this->sync_status = self::SYNC_NONE;
                $this->last_check_datetime = null;
                $this->logistics_query_raw_results = '[]';
                $this->created_at = $this->updated_at = time();
                $this->created_by = $this->updated_by = $userId;
            } else {
                if (!IsHelper::json($this->logistics_query_raw_results)) {
                    $this->logistics_query_raw_results = '[]';
                }
                $this->updated_at = time();
                $this->updated_by = $userId;
            }

            return true;
        } else {
            return false;
        }
    }

    public function afterDelete()
    {
        parent::afterDelete();
        PackageRoute::deleteAll(['package_id' => $this->id]);
    }

}

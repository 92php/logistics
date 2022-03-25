<?php

namespace app\modules\admin\modules\wuliu\models;

use app\models\Constant;
use app\models\Member;
use Yii;

/**
 * This is the model class for table "{{%wuliu_package_route}}".
 *
 * @property int $id
 * @property int $package_id 包裹
 * @property int $line_route_id 线路路由
 * @property int $compute_method 估算方式
 * @property int $compute_reference_value 估算参考值
 * @property int $begin_datetime 开始时间
 * @property int $plan_datetime 预测时间
 * @property int $plan_datetime_is_changed 是否修改
 * @property int $end_datetime 抵达时间
 * @property int $take_minutes 耗费时间
 * @property int $status 状态
 * @property int $process_status 处理状态
 * @property int $process_member_id 处理人
 * @property int $process_datetime 处理时间
 * @property string|null $remark 备注
 */
class PackageRoute extends \yii\db\ActiveRecord
{

    /**
     * 估算方式
     */
    const COMPUTE_METHOD_NONE = 0;
    const COMPUTE_METHOD_AUTO = 1;
    const COMPUTE_METHOD_MANUAL = 2;

    /**
     * 状态
     */
    const STATUS_UNKNOWN = 0; // 未知
    const STATUS_NORMAL = 1; // 正常
    const STATUS_MAYBE_NORMAL = 11; // 可能正常
    const STATUS_OVERTIME = 2; // 超时
    const STATUS_MAYBE_OVERTIME = 22; // 可能超时
    const STATUS_IN_ADVANCE = 3; // 提前
    const STATUS_MAYBE_IN_ADVANCE = 33; // 可能提前

    /**
     * 处理状态
     */
    const PROCESS_STATUS_NOTHING = 0; // 无需处理
    const PROCESS_STATUS_PENDING = 1; // 待处理
    const PROCESS_STATUS_IGNORE = 2; // 忽略
    const PROCESS_STATUS_COMPLETED = 3; // 已经处理

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%wuliu_package_route}}';
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
            [['package_id', 'line_route_id', 'compute_method', 'compute_reference_value', 'plan_datetime'], 'required'],
            [['package_id', 'line_route_id', 'compute_method', 'compute_reference_value', 'begin_datetime', 'plan_datetime', 'end_datetime', 'take_minutes', 'status', 'process_status', 'process_member_id', 'process_datetime'], 'integer'],
            ['plan_datetime_is_changed', 'boolean'],
            ['plan_datetime_is_changed', 'default', 'value' => Constant::BOOLEAN_FALSE],
            ['status', 'default', 'value' => self::STATUS_UNKNOWN],
            ['status', 'in', 'range' => array_keys(self::statusOptions())],
            ['process_status', 'default', 'value' => self::PROCESS_STATUS_NOTHING],
            ['process_status', 'in', 'range' => array_keys(self::handledStatusOptions())],
            ['process_member_id', 'default', 'value' => 0],
            [['remark'], 'trim'],
            [['remark'], 'string'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => '编号',
            'package_id' => '包裹',
            'line_route_id' => '线路路由',
            'compute_method' => '估算方式',
            'compute_reference_value' => '估算参考值',
            'begin_datetime' => '开始时间',
            'plan_datetime' => '预测时间',
            'plan_datetime_is_changed' => '是否修改',
            'end_datetime' => '抵达时间',
            'take_minutes' => '耗费时间',
            'status' => '状态',
            'process_status' => '处理状态',
            'process_member_id' => '处理人',
            'process_datetime' => '处理时间',
            'remark' => '备注',
        ];
    }

    /**
     * 估算方式
     *
     * @return array
     */
    public static function computeMethodOptions()
    {
        return [
            self::COMPUTE_METHOD_NONE => '无',
            self::COMPUTE_METHOD_AUTO => '自动估算',
            self::COMPUTE_METHOD_MANUAL => '手动设置',
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
            self::STATUS_UNKNOWN => '未知',
            self::STATUS_NORMAL => '正常',
            self::STATUS_MAYBE_NORMAL => '可能正常',
            self::STATUS_OVERTIME => '超时',
            self::STATUS_MAYBE_OVERTIME => '可能超时',
            self::STATUS_IN_ADVANCE => '提前',
            self::STATUS_MAYBE_IN_ADVANCE => '可能提前',
        ];
    }

    /**
     * 处理状态
     *
     * @return array
     */
    public static function handledStatusOptions()
    {
        return [
            self::PROCESS_STATUS_NOTHING => '无需处理',
            self::PROCESS_STATUS_PENDING => '待处理',
            self::PROCESS_STATUS_IGNORE => '忽略',
            self::PROCESS_STATUS_COMPLETED => '已经处理',
        ];
    }

    /**
     * 线路路由
     *
     * @return \yii\db\ActiveQuery
     */
    public function getLineRoute()
    {
        return $this->hasOne(CompanyLineRoute::class, ['id' => 'line_route_id']);
    }

    /**
     * 处理人
     *
     * @return \yii\db\ActiveQuery
     */
    public function getProcessMember()
    {
        return $this->hasOne(Member::class, ['id' => 'process_member_id']);
    }

    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            if (in_array($this->process_status, [self::PROCESS_STATUS_IGNORE, self::PROCESS_STATUS_COMPLETED])) {
                $this->process_member_id = Yii::$app->getUser()->getId();
                $this->process_datetime = time();
            }

            return true;
        } else {
            return false;
        }
    }

}

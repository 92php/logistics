<?php

namespace app\modules\admin\modules\g\models;

use app\models\Option;
use Yii;

/**
 * This is the model class for table "{{%g_customer}}".
 *
 * @property int $id
 * @property int $platform_id 所属平台
 * @property string|null $key 外部系统编号
 * @property string|null $email 邮箱
 * @property string $first_name 姓
 * @property string|null $last_name 名
 * @property string|null $phone 联系电话
 * @property string|null $currency 货币
 * @property string|null $remark 备注
 * @property int $status 状态
 * @property int $created_at 添加时间
 * @property int $created_by 添加人
 * @property int $updated_at 更新时间
 * @property int $updated_by 更新人
 */
class Customer extends \yii\db\ActiveRecord
{

    /**
     * 状态
     */
    const STATUS_ACTIVE = 1;
    const STATUS_INVALID = 2;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%g_customer}}';
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
            [['platform_id', 'first_name'], 'required'],
            [['key', 'email', 'first_name', 'last_name', 'phone', 'currency', 'remark'], 'trim'],
            [['platform_id', 'status', 'created_at', 'created_by', 'updated_at', 'updated_by'], 'integer'],
            [['remark'], 'string'],
            [['key', 'phone'], 'string', 'max' => 30],
            [['email'], 'string', 'max' => 100],
            [['email'], 'email'],
            [['email'], 'unique', 'when' => function ($model) {
                return $model->email;
            }],
            [['first_name', 'last_name'], 'string', 'max' => 20],
            [['currency'], 'filter', 'filter' => 'strtoupper'],
            [['currency'], 'string', 'max' => 3],
            ['platform_id', 'default', 'value' => 0],
            ['platform_id', 'in', 'range' => array_keys(Option::platforms())],
            ['status', 'default', 'value' => self::STATUS_ACTIVE],
            ['status', 'in', 'range' => array_keys(self::statusOptions())],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => '编号',
            'platform_id' => '所属平台',
            'key' => '外部系统编号',
            'email' => '邮箱',
            'first_name' => '姓',
            'last_name' => '名',
            'full_name' => '姓名',
            'phone' => '联系电话',
            'currency' => '货币',
            'remark' => '备注',
            'status' => '状态',
            'created_at' => '添加时间',
            'created_by' => '添加人',
            'updated_at' => '更新时间',
            'updated_by' => '更新人',
        ];
    }

    /**
     * 姓名
     *
     * @return string
     */
    public function getFull_name()
    {
        return $this->first_name . ($this->first_name ? ' ' : '') . $this->last_name;
    }

    /**
     * 客户地址列表
     *
     * @return \yii\db\ActiveQuery
     */
    public function getAddresses()
    {
        return $this->hasMany(CustomerAddress::class, ['customer_id' => 'id']);
    }

    /**
     * 状态选项
     *
     * @return array
     */
    public static function statusOptions()
    {
        return [
            self::STATUS_ACTIVE => '有效',
            self::STATUS_INVALID => '无效',
        ];
    }

    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            if ($insert) {
                $this->created_at = $this->updated_at = time();
                $this->created_by = $this->updated_by = Yii::$app->getUser()->getId();
            } else {
                $this->updated_at = time();
                $this->updated_by = Yii::$app->getUser()->getId();
            }

            return true;
        } else {
            return false;
        }
    }

    public function afterDelete()
    {
        parent::afterDelete();
        CustomerAddress::deleteAll(['customer_id' => $this->id]);
    }

}

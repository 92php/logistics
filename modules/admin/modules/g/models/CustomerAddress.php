<?php

namespace app\modules\admin\modules\g\models;

use Yii;

/**
 * This is the model class for table "{{%g_customer_address}}".
 *
 * @property int $id
 * @property int $customer_id 客户
 * @property string|null $key 外部系统编号
 * @property string $first_name 姓
 * @property string|null $last_name 名
 * @property string|null $company 公司
 * @property string|null $address1 地址 1
 * @property string|null $address2 地址 2
 * @property int $country_id 国家
 * @property string|null $province 省/州
 * @property string|null $city 城市
 * @property string|null $zip 邮编
 * @property string|null $phone 联系电话
 */
class CustomerAddress extends \yii\db\ActiveRecord
{

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%g_customer_address}}';
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
            [['customer_id', 'first_name'], 'required'],
            [['key', 'first_name', 'last_name', 'address1', 'address2', 'province', 'city', 'zip', 'phone'], 'trim'],
            [['customer_id', 'country_id'], 'integer'],
            ['country_id', 'default', 'value' => 0],
            [['key', 'phone'], 'string', 'max' => 30],
            [['first_name', 'last_name', 'zip'], 'string', 'max' => 20],
            [['company', 'address1', 'address2'], 'string', 'max' => 200],
            [['province', 'city'], 'string', 'max' => 50],
            ['customer_id',
                'exist',
                'targetClass' => Customer::class,
                'targetAttribute' => ['customer_id' => 'id'],
            ],
            ['country_id',
                'exist',
                'targetClass' => Country::class,
                'targetAttribute' => ['country_id' => 'id'],
                'when' => function ($model) {
                    return $model->country_id != 0;
                }
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'customer_id' => '客户',
            'key' => '外部系统编号',
            'first_name' => '姓',
            'last_name' => '名',
            'company' => '公司',
            'address1' => '地址 1',
            'address2' => '地址 2',
            'country_id' => '国家',
            'province' => '省/州',
            'city' => '城市',
            'zip' => '邮编',
            'phone' => '联系电话',
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
     * 所属客户
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCustomer()
    {
        return $this->hasOne(Customer::class, ['id' => 'country_id']);
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

}

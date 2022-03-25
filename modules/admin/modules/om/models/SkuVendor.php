<?php

namespace app\modules\admin\modules\om\models;

use app\models\Constant;
use app\modules\admin\modules\g\models\Vendor;
use Yii;
use yii\validators\CompareValidator;

/**
 * This is the model class for table "{{%om_sku_vendor}}".
 *
 * @property int $id
 * @property int $ordering 排序
 * @property string $sku SKU
 * @property int $vendor_id 供应商
 * @property float $cost_price 成本价
 * @property int $production_min_days 生产最小天数
 * @property int $production_max_days 生产最大天数
 * @property int $enabled 激活
 * @property string|null $remark 备注
 * @property int $created_at 添加时间
 * @property int $created_by 添加人
 * @property int $updated_at 更新时间
 * @property int $updated_by 更新人
 */
class SkuVendor extends \yii\db\ActiveRecord
{

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%om_sku_vendor}}';
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
            [['ordering', 'vendor_id'], 'integer'],
            ['enabled', 'boolean'],
            ['enabled', 'default', 'value' => Constant::BOOLEAN_TRUE],
            [['sku', 'vendor_id'], 'required'],
            [['sku', 'remark'], 'trim'],
            [['cost_price'], 'number'],
            [['remark'], 'string'],
            [['sku'], 'string', 'max' => 40],
            [['production_min_days', 'production_max_days'], 'integer', 'min' => 1, 'max' => 127],
            ['production_min_days', 'compare',
                'type' => CompareValidator::TYPE_NUMBER,
                'operator' => '<=',
                'compareAttribute' => 'production_max_days'
            ],
            [['sku'], 'unique', 'targetAttribute' => ['sku', 'vendor_id']],
            ['vendor_id', 'exist',
                'targetClass' => Vendor::class,
                'targetAttribute' => 'id',
            ]
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => '编号',
            'ordering' => '排序',
            'sku' => 'SKU',
            'vendor_id' => '供应商',
            'cost_price' => '成本价',
            'production_min_days' => '生产最小天数',
            'production_max_days' => '生产最大天数',
            'enabled' => '激活',
            'remark' => '备注',
            'created_at' => '添加时间',
            'created_by' => '添加人',
            'updated_at' => '更新时间',
            'updated_by' => '更新人',
        ];
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

}

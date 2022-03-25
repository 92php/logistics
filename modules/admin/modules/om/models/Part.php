<?php

namespace app\modules\admin\modules\om\models;

use app\modules\api\models\Constant;
use app\modules\admin\modules\g\models\Vendor;
use Yii;

/**
 * This is the model class for table "{{%om_parts}}".
 *
 * @property int $id
 * @property int $sn 序列号
 * @property string $sku SKU
 * @property string|null $customized 定制名
 * @property int|null $is_empty 是否为空
 * @property int $order_item_id 商品id
 * @property int $vendor_id 供应商
 * @property int $created_at 添加时间
 * @property int $created_by 添加人
 * @property int $updated_at 更新时间
 * @property int $updated_by 更新人
 */
class Part extends \yii\db\ActiveRecord
{

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%om_part}}';
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
            [['sku'], 'required'],
            [['is_empty', 'created_at', 'created_by', 'updated_at', 'updated_by', 'sn', 'vendor_id', 'order_item_id'], 'integer'],
            [['sku'], 'string', 'max' => 40],
            [['customized'], 'string', 'max' => 50],
            ['is_empty', 'default', 'value' => Constant::BOOLEAN_FALSE],
            ['vendor_id', 'exist', 'targetClass' => Vendor::class, 'targetAttribute' => 'id']
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'sn' => '序列号',
            'sku' => 'Sku',
            'customized' => '定制名',
            'is_empty' => '是否为空',
            'order_item_id' => '商品id',
            'vendor_id' => '供应商',
            'created_at' => '添加时间',
            'created_by' => '添加人',
            'updated_at' => '修改时间',
            'updated_by' => '修改人',
        ];
    }

    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            if ($insert) {
                $this->created_by = $this->updated_by = Yii::$app->getUser()->getId();
                $this->created_at = $this->updated_at = time();
            } else {
                $this->updated_by = Yii::$app->getUser()->getId();
                $this->updated_at = time();
            }

            return true;
        } else {
            return false;
        }
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

}

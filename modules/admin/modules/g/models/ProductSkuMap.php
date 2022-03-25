<?php

namespace app\modules\admin\modules\g\models;

/**
 * This is the model class for table "{{%g_product_sku_map}}".
 *
 * @property int $id
 * @property int $product_id 商品
 * @property string $value 商品 SKU
 */
class ProductSkuMap extends \yii\db\ActiveRecord
{

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%g_product_sku_map}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['value'], 'required'],
            [['product_id'], 'integer'],
            [['value'], 'string', 'max' => 128],
            [['product_id', 'value'], 'unique', 'targetAttribute' => ['product_id', 'value']],
            ['product_id', 'exist',
                'targetClass' => Product::class,
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
            'id' => 'id',
            'product_id' => '产品id',
            'value' => 'value',
        ];
    }
}

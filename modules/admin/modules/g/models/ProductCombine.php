<?php

namespace app\modules\admin\modules\g\models;

/**
 * This is the model class for table "{{%g_product_combine}}".
 *
 * @property int $id
 * @property int $product_id 所属产品
 * @property int $child_product_id 子级产品
 */
class ProductCombine extends \yii\db\ActiveRecord
{

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%g_product_combine}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['product_id', 'child_product_id'], 'required'],
            [['product_id', 'child_product_id'], 'integer'],
            ['child_product_id', 'exist',
                'targetClass' => Product::class,
                'targetAttribute' => 'id',
            ],
            ['product_id', 'exist',
                'targetClass' => Product::class,
                'targetAttribute' => 'id',
            ],
            [['product_id', 'child_product_id'], 'unique', 'targetAttribute' => ['product_id', 'child_product_id']],
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
            'child_product_id' => '子类产品id',
        ];
    }
}

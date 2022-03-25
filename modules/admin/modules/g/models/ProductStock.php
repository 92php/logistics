<?php

namespace app\modules\admin\modules\g\models;

/**
 * This is the model class for table "{{%g_product_stock}}".
 *
 * @property int $id
 * @property int $product_id 商品
 * @property int $warehouse_id 仓库
 * @property int $block 分区
 * @property int $rack_id 货架
 * @property int|null $safely_quantity 安全库存数量
 * @property int|null $booking_quantity 预售库存数量
 * @property int|null $trip_quantity 在途库存数量
 * @property int|null $usable_quantity 可用库存数量
 * @property int|null $actual_quantity 实际库存数量
 * @property float|null $price 价格
 * @property float|null $total_price 总价
 * @property string|null $remark 备注
 * @property int $created_at 创建时间
 * @property int $updated_at 修改时间
 */
class ProductStock extends \yii\db\ActiveRecord
{

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%g_product_stock}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['product_id', 'warehouse_id', 'block', 'rack_id'], 'required'],
            [['product_id', 'warehouse_id', 'block', 'rack_id', 'safely_quantity', 'booking_quantity', 'trip_quantity', 'usable_quantity', 'actual_quantity', 'created_at', 'updated_at'], 'integer'],
            [['price', 'total_price'], 'number', 'min' => 0.01],
            ['block', 'in', 'range' => array_keys(Rack::blockOptions())],
            [['safely_quantity', 'booking_quantity', 'trip_quantity', 'usable_quantity', 'actual_quantity'], 'default', 'value' => 0],
            [['remark'], 'string'],
            ['product_id', 'exist',
                'targetClass' => Product::class,
                'targetAttribute' => 'id',
            ],
            ['warehouse_id', 'exist',
                'targetClass' => Warehouse::class,
                'targetAttribute' => 'id',
            ],
            ['rack_id', 'exist',
                'targetClass' => Rack::class,
                'targetAttribute' => 'id',
            ],
            [['product_id', 'warehouse_id', 'block', 'rack_id'], 'unique', 'targetAttribute' => ['product_id', 'warehouse_id', 'block', 'rack_id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'product_id' => '产品',
            'warehouse_id' => '仓库',
            'block' => '分区',
            'rack_id' => '货架',
            'safely_quantity' => '安全库存数量',
            'booking_quantity' => '预售库存数量',
            'trip_quantity' => '在途库存数量',
            'usable_quantity' => '可用库存数量',
            'actual_quantity' => '实际库存数量',
            'price' => '价格',
            'total_price' => '总价',
            'remark' => '备注',
            'created_at' => '创建时间',
            'updated_at' => '更新时间',
        ];
    }

    /**
     * 产品
     *
     * @return \yii\db\ActiveQuery
     */
    public function getProduct()
    {
        return $this->hasOne(Product::class, ['id' => 'product_id']);
    }

    /**
     * 仓库
     *
     * @return \yii\db\ActiveQuery
     */
    public function getWarehouse()
    {
        return $this->hasOne(Warehouse::class, ['id' => 'warehouse_id']);
    }

    /**
     * 货架
     *
     * @return \yii\db\ActiveQuery
     */
    public function getRack()
    {
        return $this->hasOne(Rack::class, ['id' => 'rack_id']);
    }

    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            if ($insert) {
                $this->created_at = $this->updated_at = time();
            } else {
                $this->updated_at = time();
            }
            $this->total_price = $this->actual_quantity * $this->price;

            return true;
        } else {
            return false;
        }
    }

}

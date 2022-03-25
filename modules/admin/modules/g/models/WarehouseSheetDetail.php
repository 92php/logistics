<?php

namespace app\modules\admin\modules\g\models;

use yii\db\ActiveQuery;

/**
 * This is the model class for table "{{%g_warehouse_sheet_detail}}".
 *
 * @property int $id
 * @property int $warehouse_sheet_id 出入库路单号
 * @property int $warehouse_id 仓库
 * @property int $rack_id 货架位
 * @property int $product_id 商品
 * @property int $before_stock_quantity 原库存
 * @property int $change_quantity 变动数量
 * @property int $after_stock_quantity 新库存
 * @property float|null $before_price 原单价
 * @property float|null $change_price 出入库价格
 * @property float|null $after_price 新单价
 */
class WarehouseSheetDetail extends \yii\db\ActiveRecord
{

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%g_warehouse_sheet_detail}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['warehouse_sheet_id', 'warehouse_id', 'rack_id', 'product_id', 'before_stock_quantity', 'change_quantity', 'after_stock_quantity'], 'required'],
            [['warehouse_sheet_id', 'warehouse_id', 'rack_id', 'product_id', 'before_stock_quantity', 'change_quantity', 'after_stock_quantity'], 'integer'],
            [['before_price', 'change_price', 'after_price'], 'number'],
            ['warehouse_id', 'exist',
                'targetClass' => Warehouse::class,
                'targetAttribute' => 'id',
            ],
            ['warehouse_sheet_id', 'exist',
                'targetClass' => WarehouseSheet::class,
                'targetAttribute' => 'id',
            ],
            ['rack_id', 'exist',
                'targetClass' => Rack::class,
                'targetAttribute' => 'id',
            ],
            ['product_id', 'exist',
                'targetClass' => Product::class,
                'targetAttribute' => 'id',
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'id',
            'warehouse_sheet_id' => '出入库单号',
            'warehouse_id' => '仓库',
            'rack_id' => '货架位',
            'product_id' => '产品',
            'before_stock_quantity' => '原库存',
            'change_quantity' => '变动数量',
            'after_stock_quantity' => '新库存',
            'before_price' => '原单价',
            'change_price' => '出入库价格',
            'after_price' => '新单价',
        ];
    }

    /**
     * 出入库单
     *
     * @return ActiveQuery
     */
    public function getWarehouseSheet()
    {
        return $this->hasOne(WarehouseSheet::class, ['id' => 'warehouse_sheet_id']);
    }

    /**
     * 仓库
     *
     * @return ActiveQuery
     */
    public function getWarehouse()
    {
        return $this->hasOne(Warehouse::class, ['id' => 'warehouse_id']);
    }

    /**
     * 货架
     *
     * @return ActiveQuery
     */
    public function getRack()
    {
        return $this->hasOne(Rack::class, ['id' => 'rack_id']);
    }

    /**
     * 商品
     *
     * @return ActiveQuery
     */
    public function getProduct()
    {
        return $this->hasOne(Product::class, ['id' => 'product_id']);
    }

}

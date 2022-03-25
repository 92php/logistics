<?php

namespace app\modules\api\modules\g\models;

/**
 * Class ProductStock
 *
 * @package app\modules\api\modules\g\models
 */
class ProductStock extends \app\modules\admin\modules\g\models\ProductStock
{

    public function attributeLabels()
    {
        return [
            'id',
            'product_id',
            'warehouse_id',
            'block',
            'rack_id',
            'safely_quantity',
            'booking_quantity',
            'trip_quantity',
            'usable_quantity',
            'actual_quantity',
            'price',
            'total_price',
            'remark',
            'created_at',
            'updated_at',
        ];
    }

    /**
     * 产品
     */
    public function getProduct()
    {
        $this->hasOne(Product::class, ['id' => 'product_id']);
    }

    /**
     * 仓库
     */
    public function getWarehouse()
    {
        $this->hasOne(Warehouse::class, ['id' => 'warehouse_id']);
    }

    /**
     * 货架
     */
    public function getRack()
    {
        $this->hasOne(Rack::class, ['id' => 'rack_id']);
    }

    public function extraFields()
    {
        return ['rack,', 'warehouse', 'product'];
    }

}

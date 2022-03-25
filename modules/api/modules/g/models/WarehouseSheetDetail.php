<?php

namespace app\modules\api\modules\g\models;

use yii\db\ActiveQuery;

/**
 * Class WarehouseSheetDetail
 *
 * @package app\modules\api\modules\g\models
 */
class WarehouseSheetDetail extends \app\modules\admin\modules\g\models\WarehouseSheetDetail
{

    public function fields()
    {
        return [
            'id',
            'warehouse_sheet_id',
            'warehouse_id',
            'rack_id',
            'product_id',
            'before_stock_quantity',
            'change_quantity',
            'after_stock_quantity',
            'before_price',
            'change_price',
            'after_price',
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

    public function extraFields()
    {
        return ['warehouse', 'warehouse-sheet' => 'warehouseSheet', 'rack', 'product'];
    }
}

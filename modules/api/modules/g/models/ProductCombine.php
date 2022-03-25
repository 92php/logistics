<?php

namespace app\modules\api\modules\g\models;

use yii\db\ActiveQuery;

/**
 * Class ProductCombine
 *
 * @package app\modules\api\modules\g\models
 */
class ProductCombine extends \app\modules\admin\modules\g\models\ProductCombine
{

    public function fields()
    {
        return [
            'id',
            'product_id',
            'child_product_id',
        ];
    }

    /**
     * 子类商品
     *
     * @return ActiveQuery
     */
    public function getChildProduct()
    {
        return $this->hasOne(Product::class, ['child_product_id' => 'id']);
    }

    public function extraFields()
    {
        return ['childProduct' => 'child-product'];
    }

}

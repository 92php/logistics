<?php

namespace app\modules\api\modules\g\models;

/**
 * Class ProductSkuMap
 *
 * @package app\modules\api\modules\g\models
 */
class ProductSkuMap extends \app\modules\admin\modules\g\models\ProductSkuMap
{

    public function fields()
    {
        return [
            'id',
            'product_id',
            'value',
        ];
    }
}

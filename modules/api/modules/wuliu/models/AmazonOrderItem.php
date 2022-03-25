<?php

namespace app\modules\api\modules\wuliu\models;

class AmazonOrderItem extends \app\modules\admin\modules\wuliu\models\AmazonOrderItem
{

    public function fields()
    {
        return [
            'id',
            'order_id',
            'product_name',
            'product_image',
            'product_quantity',
            'size',
            'color',
            'customized',
        ];
    }
}
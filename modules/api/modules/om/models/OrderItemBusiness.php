<?php

namespace app\modules\api\modules\om\models;

/**
 * 订单业务模型
 *
 * @package app\modules\api\modules\om\models
 */
class OrderItemBusiness extends \app\modules\admin\modules\om\models\OrderItemBusiness
{

    public function fields()
    {
        return [
            'id',
            'priority',
            'order_item_id',
            'status',
        ];
    }

}

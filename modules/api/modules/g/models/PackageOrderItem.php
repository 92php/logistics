<?php

namespace app\modules\api\modules\g\models;

class PackageOrderItem extends \app\modules\admin\modules\g\models\PackageOrderItem
{

    public function fields()
    {
        return [
            'id',
            'package_id',
            'order_id',
            'order_item_id',
        ];
    }

}
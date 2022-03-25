<?php

namespace app\modules\api\modules\om\models;

class Package extends \app\modules\admin\modules\om\models\Package
{

    public function fields()
    {
        return [
            'id',
            'waybill_number',
            'number',
            'title',
            'logistics_company',
            'items_quantity',
            'remaining_items_quantity',
            'status',
            'created_at',
            'created_by',
            'updated_at',
            'updated_by',
        ];
    }

}
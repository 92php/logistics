<?php

namespace app\modules\api\modules\om\models;

/**
 * Class Part
 *
 * @package app\modules\api\modules\om\models
 */
class Part extends \app\modules\admin\modules\om\models\Part
{

    public function fields()
    {
        return [
            'id',
            'sn',
            'sku',
            'customized',
            'is_empty' => function ($model) {
                return boolval($model->is_empty);
            },
            'vendor_id',
            'order_item_id',
            'created_at',
            'created_by',
            'updated_at',
            'updated_by',
        ];
    }

}
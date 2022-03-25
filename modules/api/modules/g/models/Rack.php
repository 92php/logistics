<?php

namespace app\modules\api\modules\g\models;

/**
 * Class Rack
 *
 * @package app\modules\api\modules\g\models
 */
class Rack extends \app\modules\admin\modules\g\models\Rack
{

    public function fields()
    {
        return [
            'id',
            'warehouse_id',
            'block',
            'number',
            'priority',
            'remark',
            'created_at',
            'created_by',
            'updated_at',
            'updated_by',
        ];
    }

}

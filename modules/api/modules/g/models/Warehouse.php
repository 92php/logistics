<?php

namespace app\modules\api\modules\g\models;

class Warehouse extends \app\modules\admin\modules\g\models\Warehouse
{

    public function fields()
    {
        return [
            'id',
            'name',
            'address',
            'linkman',
            'tel',
            'remark',
            'enabled' => function ($model) {
                return boolval($model->enabled);
            },
            'created_at',
            'created_by',
            'updated_at',
            'updated_by',
        ];
    }

}
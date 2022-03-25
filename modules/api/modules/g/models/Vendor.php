<?php

namespace app\modules\api\modules\g\models;

class Vendor extends \app\modules\admin\modules\g\models\Vendor
{

    public function fields()
    {
        return [
            'id',
            'name',
            'address',
            'tel',
            'linkman',
            'mobile_phone',
            'receipt_duration',
            'production',
            'credibility',
            'enabled' => function ($model) {
                return boolval($model->enabled);
            },
            'remark',
            'created_at',
            'created_by',
            'updated_at',
            'updated_by',
        ];
    }

}

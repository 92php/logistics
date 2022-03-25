<?php

namespace app\modules\api\modules\wuliu\models;

class FreightTemplate extends \app\modules\admin\modules\wuliu\models\FreightTemplate
{

    public function fields()
    {
        return [
            'id',
            'company_id',
            'name',
            'fee_mode',
            'enabled' => function ($model) {
                return boolval($model['enabled']);
            },
            'remark',
            'created_at',
            'created_by',
            'updated_at',
            'updated_by',
        ];
    }

}
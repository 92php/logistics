<?php

namespace app\modules\api\modules\g\models;

class ThirdPartyAuthentication extends \app\modules\admin\modules\g\models\ThirdPartyAuthentication
{

    public function fields()
    {
        return [
            'id',
            'platform_id',
            'name',
            'authentication_config',
            'configurations',
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
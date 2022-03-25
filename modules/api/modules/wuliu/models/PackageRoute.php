<?php

namespace app\modules\api\modules\wuliu\models;

class PackageRoute extends \app\modules\admin\modules\wuliu\models\PackageRoute
{

    public function fields()
    {
        return [
            'id',
            'package_id',
            'line_route_id',
            'compute_method',
            'compute_reference_value',
            'begin_datetime',
            'plan_datetime',
            'plan_datetime_is_changed' => function ($model) {
                return boolval($model['plan_datetime_is_changed']);
            },
            'end_datetime',
            'take_minutes',
            'status',
            'process_status',
            'process_member_id',
            'process_datetime',
            'remark',
        ];
    }

}

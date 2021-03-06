<?php

namespace app\modules\api\models;

/**
 * Class MemberLoginLog
 *
 * @package app\modules\api\models

 */
class MemberLoginLog extends BaseMemberLoginLog
{

    public function fields()
    {
        return [
            'id',
            'ip',
            'login_at',
            'client_information',
        ];
    }

}

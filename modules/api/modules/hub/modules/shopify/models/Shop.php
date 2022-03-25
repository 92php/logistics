<?php

namespace app\modules\api\modules\hub\models;

/**
 * `shop` 接口类
 *
 * @package app\modules\api\modules\hub\models
 */
class Shop extends \app\modules\admin\modules\hub\modules\shopify\models\Shop
{

    public function fields()
    {
        return [
            'id',
            'project_id',
            'key',
            'name',
            'url',
            'api_key',
            'api_password',
            'api_shared_secret',
            'webhooks_shared_secret',
            'fixed_fee',
            'remark',
            'enabled' => function ($model) {
                return boolval($model->enabled);
            },
            'created_at',
            'created_by',
            'updated_at',
            'updated_by'
        ];
    }

}
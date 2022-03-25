<?php

namespace app\modules\api\modules\g\models;

/**
 * Class Tag
 *
 * @package app\modules\api\modules\g\models
 */
class Tag extends \app\modules\admin\modules\g\models\Tag
{

    public function fields()
    {
        return [
            'id',
            'type',
            'parent_id',
            'name',
            'ordering',
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

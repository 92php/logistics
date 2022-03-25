<?php

namespace app\modules\api\modules\g\models;

class CustomsDeclarationDocument extends \app\modules\admin\modules\g\models\CustomsDeclarationDocument
{

    public function fields()
    {
        return [
            'id',
            'code',
            'chinese_name',
            'english_name',
            'weight',
            'amount',
            'danger_level',
            'default',
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

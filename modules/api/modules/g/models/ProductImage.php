<?php

namespace app\modules\api\modules\g\models;

use yadjet\helpers\IsHelper;
use Yii;

/**
 * Class ProductImage
 *
 * @package app\modules\api\modules\g\models
 */
class ProductImage extends \app\modules\admin\modules\g\models\ProductImage
{

    public function attributeLabels()
    {
        return [
            'id',
            'product_id',
            'title',
            'path',
            'ordering',
            'created_at',
            'created_by',
            'updated_at',
            'updated_by',
        ];
    }
}

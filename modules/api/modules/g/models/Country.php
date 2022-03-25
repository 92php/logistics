<?php

namespace app\modules\api\modules\g\models;

use app\modules\admin\modules\g\extensions\Formatter;
use Yii;

class Country extends \app\modules\admin\modules\g\models\Country
{

    public function fields()
    {
        return [
            'id',
            'region_id',
            'region_name' => function ($model) {
                /* @var $formatter Formatter */
                $formatter = Yii::$app->getFormatter();

                return $formatter->asCountryRegion($model->region_id);
            },
            'abbreviation',
            'chinese_name',
            'english_name',
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
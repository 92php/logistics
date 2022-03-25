<?php

namespace app\modules\api\modules\wuliu\models;

class CompanyLineRoute extends \app\modules\admin\modules\wuliu\models\CompanyLineRoute
{

    public function fields()
    {
        return [
            'id',
            'line_id',
            'step',
            'event',
            'detection_keyword',
            'estimate_days',
            'package_status',
            'enabled' => function ($model) {
                return boolval($model->enabled);
            },
            'created_at',
            'created_by',
            'updated_at',
            'updated_by',
        ];
    }

    /**
     * 获取路线
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCompanyLine()
    {
        return $this->hasOne(CompanyLine::class, ['id' => 'line_id']);
    }

    public function extraFields()
    {
        return ['company-line'];
    }

}
<?php

namespace app\modules\api\modules\wuliu\models;

class Company extends \app\modules\admin\modules\wuliu\models\Company
{

    public function fields()
    {
        return [
            'id',
            'name',
            'code',
            'website_url',
            'linkman',
            'mobile_phone',
            'remark',
            'enabled' => function ($model) {
                return boolval($model->enabled);
            },
            'created_at',
            'created_by',
            'updated_at',
            'updated_by',
        ];
    }

    public function extraFields()
    {
        return ['lines'];
    }

    public function getLines()
    {
        return $this->hasMany(CompanyLine::class, ['company_id' => 'id']);
    }

}
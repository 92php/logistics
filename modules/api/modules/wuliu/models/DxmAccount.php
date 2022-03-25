<?php

namespace app\modules\api\modules\wuliu\models;

use app\modules\api\modules\wuliu\extensions\Formatter;

class DxmAccount extends \app\modules\admin\modules\wuliu\models\DxmAccount
{

    public function fields()
    {
        $formatter = new Formatter();

        return [
            'id',
            'username',
            'company_id',
            'platform_id',
            'platform_name' => function ($model) use ($formatter) {
                return $formatter->asPlatform($model->platform_id);
            },
            'is_valid' => function ($model) {
                return boolval($model->is_valid);
            },
            'remark',
            'created_at',
            'created_by',
            'updated_at',
            'updated_by',
        ];
    }

    public function extraFields()
    {
        return ['company'];
    }

    /**
     * 所属物流公司
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCompany()
    {
        return $this->hasOne(Company::class, ['id' => 'company_id']);
    }

}
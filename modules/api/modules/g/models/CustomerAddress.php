<?php

namespace app\modules\api\modules\g\models;

class CustomerAddress extends \app\modules\admin\modules\g\models\CustomerAddress
{

    public function fields()
    {
        return [
            'id',
            'customer_id',
            'key',
            'first_name',
            'last_name',
            'full_name',
            'company',
            'address1',
            'address2',
            'country_id',
            'province',
            'city',
            'zip',
            'phone',
        ];
    }

    public function extraFields()
    {
        return ['country'];
    }

    /**
     * 所属国家
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCountry()
    {
        return $this->hasOne(Country::class, ['id' => 'country_id']);
    }

}
<?php

namespace app\modules\api\modules\g\models;

class Customer extends \app\modules\admin\modules\g\models\Customer
{

    public function fields()
    {
        return [
            'id',
            'platform_id',
            'key',
            'email',
            'first_name',
            'last_name',
            'full_name',
            'phone',
            'currency',
            'remark',
            'status',
            'created_at',
            'created_by',
            'updated_at',
            'updated_by',
        ];
    }

    public function extraFields()
    {
        return ['addresses'];
    }

    /**
     * 客户地址列表
     *
     * @return \yii\db\ActiveQuery
     */
    public function getAddresses()
    {
        return $this->hasMany(CustomerAddress::class, ['customer_id' => 'id']);
    }

}
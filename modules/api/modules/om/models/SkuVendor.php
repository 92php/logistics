<?php

namespace app\modules\api\modules\om\models;

use app\modules\api\modules\g\models\Vendor;

class SkuVendor extends \app\modules\admin\modules\om\models\SkuVendor
{

    public function fields()
    {
        return [
            'id',
            'ordering',
            'sku',
            'vendor_id',
            'cost_price',
            'production_min_days',
            'production_max_days',
            'enabled' => function ($model) {
                return boolval($model['enabled']);
            },
            'remark',
            'created_at',
            'created_by',
            'updated_at',
            'updated_by',
        ];
    }

    /**
     * 所属供应商
     *
     * @return \yii\db\ActiveQuery
     */
    public function getVendor()
    {
        return $this->hasOne(Vendor::class, ['id' => 'vendor_id']);
    }

    public function extraFields()
    {
        return ['vendor'];
    }

}
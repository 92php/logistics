<?php

namespace app\modules\api\modules\wuliu\models;

class FreightTemplateFee extends \app\modules\admin\modules\wuliu\models\FreightTemplateFee
{

    public function fields()
    {
        return [
            'id',
            'template_id',
            'line_id',
            'min_weight',
            'max_weight',
            'first_weight',
            'first_fee',
            'continued_weight',
            'continued_fee',
            'fixed_fee',
            'base_fee',
            'freight_fee_rate',
            'base_fee_rate',
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
     * 所属模板
     *
     * @return \yii\db\ActiveQuery
     */
    public function getTemplate()
    {
        return $this->hasOne(FreightTemplate::class, ['id' => 'template_id']);
    }

    public function extraFields()
    {
        return ['template'];
    }

}
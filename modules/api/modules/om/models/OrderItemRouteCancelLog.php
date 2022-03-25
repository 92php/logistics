<?php

namespace app\modules\api\modules\om\models;

/**
 * 路由取消记录模型
 *
 * @package app\modules\api\modules\om\models
 */
class OrderItemRouteCancelLog extends \app\modules\admin\modules\om\models\OrderItemRouteCancelLog
{

    public function fields()
    {
        return [
            'id',
            'order_item_route_id',
            'canceled_at',
            'canceled_by',
            'canceled_reason',
            'canceled_quantity',
            'confirmed_status',
            'confirmed_at',
            'confirmed_by',
            'confirmed_message',
        ];
    }

    /**
     * 所属路由
     *
     * @return \yii\db\ActiveQuery
     */
    public function getRoute()
    {
        return $this->hasOne(OrderItemRoute::class, ['id' => 'order_item_route_id']);
    }

    public function extraFields()
    {
        return ['route'];
    }
}
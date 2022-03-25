<?php

use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model app\modules\admin\modules\om\models\OrderItemRoute */

$this->title = $model->id;
$this->params['breadcrumbs'][] = ['label' => '商品路由列表', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);

$this->params['menus'] = [
    ['label' => Yii::t('app', 'List'), 'url' => ['index']],
]
?>
<div class="order-route-view">
    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'id',
            'waybill_number',
            'package_id',
            [
                'attribute' => 'orderItem.product_name',
                'value' => function ($model) {
                    $item = $model->orderItem;

                    return $item ? \yii\helpers\Html::a($item->product_name, ['/admin/g/order/view', 'id' => $item->order_id]) : '';
                },
                'format' => 'raw',
            ],
            'place_order_at:datetime',
            'placeOrderMember.username',
            [
                'attribute' => 'vendor.name',
                'format' => 'raw',
                'value' => function ($model) {
                    $vendor = $model->vendor;

                    return $vendor ? \yii\helpers\Html::a($vendor->name, ['/admin/g/vendor/view', 'id' => $vendor->id]) : '';
                }
            ],
            'receipt_at:datetime',
            'receipt_status:orderItemRoutePlaceOrderStatus',
            'current_node:orderItemRouteStatus',
            'production_at:datetime',
            'production_status:boolean',
            'vendor_deliver_at:datetime',
            'delivery_status:boolean',
            'receiving_at:datetime',
            'receiving_status:boolean',
            'inspection_at:datetime',
            'inspection_status:boolean',
            'warehousing_at:datetime',
            'inspection_number',
            'is_reissue:boolean',
            'quantity',
            'status:boolean',
            'reason',
            'feedback:ntext',
            'information_feedback:ntext',
            'is_accord_with:boolean',
            'is_information_match:boolean',
            'cost_price',
        ],
    ]) ?>
</div>

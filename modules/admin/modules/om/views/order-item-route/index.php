<?php

use yii\grid\GridView;
use yii\widgets\Pjax;

/* @var $this yii\web\View */
/* @var $searchModel app\modules\admin\modules\om\models\OrderItemRouteSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = '商品路由列表';
$this->params['breadcrumbs'][] = $this->title;

$this->params['menus'] = [
    ['label' => Yii::t('app', 'List'), 'url' => ['index']],
];
?>
<div class="order-route-index">
    <?php Pjax::begin(); ?>
    <?= $this->render('_search', ['model' => $searchModel]); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'columns' => [
            [
                'class' => 'yii\grid\SerialColumn',
                'contentOptions' => ['class' => 'serial-number']
            ],

            [
                'attribute' => 'orderItem.product_name',
                'value' => function ($model) {
                    $item = $model->orderItem;

                    return $item ? \yii\helpers\Html::a($item->product_name, ['/admin/g/order/view', 'id' => $item->order_id]) : '';
                },
                'format' => 'raw',
            ],
            [
                'attribute' => 'quantity',
                'contentOptions' => ['class' => 'number'],
            ],
            [
                'attribute' => 'cost_price',
                'contentOptions' => ['class' => 'number'],
            ],
            [
                'attribute' => 'vendor.name',
                'format' => 'raw',
                'value' => function ($model) {
                    $vendor = $model->vendor;

                    return $vendor ? \yii\helpers\Html::a($vendor->name, ['/admin/g/vendor/view', 'id' => $vendor->id]) : '';
                }
            ],
            'current_node:orderItemRouteStatus',
            'receipt_status:orderItemRoutePlaceOrderStatus',
            [
                'attribute' => 'is_reissue',
                'format' => 'boolean',
                'contentOptions' => ['class' => 'boolean'],
            ],
            [
                'attribute' => 'production_status',
                'format' => 'boolean',
                'contentOptions' => ['class' => 'boolean'],
            ],
            [
                'attribute' => 'delivery_status',
                'format' => 'boolean',
                'contentOptions' => ['class' => 'boolean'],
            ],
            [
                'attribute' => 'receiving_status',
                'format' => 'boolean',
                'contentOptions' => ['class' => 'boolean'],
            ],
            [
                'attribute' => 'inspection_status',
                'format' => 'boolean',
                'contentOptions' => ['class' => 'boolean'],
            ],
            [
                'attribute' => 'place_order_at',
                'format' => 'datetime',
                'contentOptions' => ['class' => 'datetime'],
            ],
            [
                'class' => 'yii\grid\ActionColumn',
                'template' => '{view}',
                'headerOptions' => ['class' => 'button-1 last'],
            ],
        ],
    ]); ?>
    <?php Pjax::end(); ?>
</div>

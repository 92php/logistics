<?php

use app\modules\admin\modules\g\models\Order;
use yii\grid\GridView;
use yii\helpers\Html;
use yii\widgets\Pjax;

/* @var $this yii\web\View */
/* @var $searchModel app\modules\admin\modules\g\models\OrderSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = '订单管理';
$this->params['breadcrumbs'][] = $this->title;

$this->params['menus'] = [
    ['label' => Yii::t('app', 'List'), 'url' => ['index']],
    ['label' => '统计', 'url' => ['statistics']],
];
?>
<div class="order-index">
    <?php Pjax::begin([
        'timeout' => 6000,
    ]); ?>
    <?= $this->render('_search', ['model' => $searchModel]); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'columns' => [
            [
                'class' => 'yii\grid\SerialColumn',
                'contentOptions' => ['class' => 'serial-number']
            ],
            [
                'attribute' => 'third_party_platform_id',
                'contentOptions' => ['style' => 'width: 60px; text-align: center'],
                'format' => 'thirdPartyPlatform',
            ],
            [
                'attribute' => 'shop.name',
                'value' => function ($model) {
                    $shop = $model->shop;

                    return Html::a($shop ? $shop->name : '', ['shop/view', 'id' => $model->shop_id]);
                },
                'format' => 'raw',
            ],
            [
                'attribute' => 'key',
                'contentOptions' => ['style' => 'width: 120px;'],
            ],
            [
                'attribute' => 'number',
                'value' => function ($model) {
                    $s = Html::a($model->number, ['view', 'id' => $model->id]);
                    if ($model->type == Order::TYPE_REISSUE) {
                        $s = "【补】$s";
                    }

                    return $s;
                },
                'format' => 'raw',
            ],
            [
                'attribute' => 'country.chinese_name',
                'contentOptions' => ['style' => 'width: 60px; text-align: center'],
            ],
            'consignee_name',
            'consignee_mobile_phone',
//            'consignee_tel',
//            'consignee_state',
//            'consignee_city',
            //'consignee_address1',
            //'consignee_address2',
            //'consignee_postcode',
            [
                'attribute' => 'total_amount',
                'contentOptions' => ['class' => 'number'],
            ],
            [
                'attribute' => 'product_type',
                'format' => 'productType',
                'contentOptions' => ['style' => 'width: 60px; text-align: center'],
            ],
            //'remark:ntext',
            [
                'attribute' => 'place_order_at',
                'format' => 'datetime',
                'contentOptions' => ['class' => 'datetime']
            ],
            [
                'attribute' => 'payment_at',
                'format' => 'datetime',
                'contentOptions' => ['class' => 'datetime']
            ],
            [
                'attribute' => 'cancelled_at',
                'format' => 'datetime',
                'contentOptions' => ['class' => 'datetime']
            ],
            'cancel_reason',
            [
                'attribute' => 'closed_at',
                'format' => 'datetime',
                'contentOptions' => ['class' => 'datetime']
            ],
            [
                'attribute' => 'status',
                'format' => 'orderStatus',
                'contentOptions' => ['style' => 'width: 60px; text-align: center'],
            ],
            [
                'attribute' => 'created_at',
                'format' => 'datetime',
                'contentOptions' => ['class' => 'datetime']
            ],
            [
                'attribute' => 'updated_at',
                'format' => 'datetime',
                'headerOptions' => ['class' => 'last'],
                'contentOptions' => ['class' => 'datetime']
            ],
        ],
    ]); ?>
    <?php Pjax::end(); ?>
</div>

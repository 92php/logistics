<?php

use yii\grid\GridView;
use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $searchModel app\modules\admin\modules\om\models\OrderItemRouteCancelLogSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = '取消记录';
$this->params['breadcrumbs'][] = $this->title;

$this->params['menus'] = [
    ['label' => Yii::t('app', 'List'), 'url' => ['index']],
];
?>
<div class="cancel-log-index">
    <?= $this->render('_search', ['model' => $searchModel]); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'columns' => [
            [
                'class' => 'yii\grid\SerialColumn',
                'contentOptions' => ['class' => 'serial-number']
            ],

            [
                'label' => '取消的商品',
                'value' => function ($model) {
                    $route = $model->route;
                    if ($route) {
                        $orderItem = $route->orderItem;

                        return $orderItem ? Html::a($orderItem->product_name, ['/admin/g/order/view', 'id' => $orderItem
                            ->order_id]) : '';
                    } else {
                        return '';
                    }
                },
                'format' => 'raw',
            ],
            'type:cancelType',
            'canceled_reason',
            [
                'attribute' => 'canceled_quantity',
                'contentOptions' => ['class' => 'number']
            ],
            'confirmed_status:confirmStatus',
            [
                'attribute' => 'canceled_at',
                'format' => 'datetime',
                'contentOptions' => ['class' => 'datetime']
            ],
            [
                'attribute' => 'confirmed_at',
                'format' => 'datetime',
                'contentOptions' => ['class' => 'datetime']
            ],
            'confirmed_message:ntext',
            [
                'class' => 'yii\grid\ActionColumn',
                'template' => '{view}',
                'headerOptions' => ['class' => 'button-1 last'],
            ],
        ],
    ]); ?>
</div>

<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model app\modules\admin\modules\om\models\OrderItemRouteCancelLog */

$this->title = $model->id;
$this->params['breadcrumbs'][] = ['label' => '取消记录', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);

$this->params['menus'] = [
    ['label' => Yii::t('app', 'List'), 'url' => ['index']],
]
?>
<div class="cancel-log-view">
    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'id',
            [
                'label' => '取消的商品',
                'value' => function ($model) {
                    $route = $model->route;
                    if ($route) {
                        $orderItem = $route->orderItem;

                        return $orderItem ? Html::a($orderItem->product_name, ['order-item/view', 'id' => $orderItem
                            ->id]) : '';
                    } else {
                        return '';
                    }
                },
                'format' => 'raw',
            ],
            'type:cancelType',
            'canceled_reason:ntext',
            'canceled_quantity',
            'confirmed_status:confirmStatus',
            'canceled_at:datetime',
            [
                'attribute' => 'requestMember.username',
                'label' => '取消人',
            ],
            'confirmed_at:datetime',
            [
                'attribute' => 'confirmMember.username',
                'label' => '确认人',
            ],
            'confirmed_message:ntext',

        ],
    ]) ?>
</div>

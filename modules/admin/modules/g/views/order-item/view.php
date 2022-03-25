<?php

use yii\helpers\VarDumper;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model app\modules\admin\modules\g\models\OrderItem */

$this->title = $model->id;
$this->params['breadcrumbs'][] = ['label' => '订单商品管理', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);

$this->params['menus'] = [
    ['label' => Yii::t('app', 'List'), 'url' => ['index']],
    ['label' => Yii::t('app', 'Update'), 'url' => ['update', 'id' => $model->id]]
];
?>
<div class="order-item-view">
    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'id',
            'ignored:boolean',
            'order.number',
            'product_id',
            'sku',
            'product_name',
            [
                'attribute' => 'extend',
                'format' => 'raw',
                'value' => function ($model) {
                    $extend = $model->extend;
                    if (is_array($extend)) {
                        $extend = VarDumper::dumpAsString($extend, 10, true);
                    }

                    return $extend;
                },
            ],
            'quantity',
            'vendor.name',
            'sale_price',
            'cost_price',
            'remark:ntext',
        ],
    ]) ?>
</div>

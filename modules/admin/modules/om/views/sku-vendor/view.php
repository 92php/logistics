<?php

use yii\web\YiiAsset;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model app\modules\admin\modules\om\models\SkuVendor */

$this->title = $model->sku;
$this->params['breadcrumbs'][] = ['label' => 'SKU 供应商匹配管理', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
YiiAsset::register($this);

$this->params['menus'] = [
    ['label' => Yii::t('app', 'List'), 'url' => ['index']],
    ['label' => Yii::t('app', 'Create'), 'url' => ['create']],
    ['label' => Yii::t('app', 'Update'), 'url' => ['update', 'id' => $model->id]]
];
?>
<div class="sku-vendor-view">
    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'id',
            'ordering',
            'sku',
            [
                'attribute' => 'vendor.name',
                'format' => 'raw',
                'value' => function ($model) {
                    $vendor = $model->vendor;

                    return $vendor ? \yii\helpers\Html::a($vendor->name, ['/admin/g/vendor/view', 'id' => $vendor->id]) : '';
                }
            ],
            'cost_price',
            [
                'label' => '生产天数',
                'value' => function ($model) {
                    return $model->production_min_days . ' ~ ' . $model->production_max_days;
                }
            ],
            'enabled:boolean',
            'remark:ntext',
            'created_at:datetime',
            'updated_at:datetime',
        ],
    ]) ?>
</div>

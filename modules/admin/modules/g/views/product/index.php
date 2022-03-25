<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;

/* @var $this yii\web\View */
/* @var $searchModel app\modules\admin\modules\g\models\ProductSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = '商品管理';
$this->params['breadcrumbs'][] = $this->title;

$this->params['menus'] = [
    ['label' => Yii::t('app', 'List'), 'url' => ['index']],
    ['label' => Yii::t('app', 'Create'), 'url' => ['create']],
];
?>
<div class="product-index">
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
                'attribute' => 'category.name',
                'contentOptions' => ['style' => 'width: 80px;'],
            ],
            [
                'attribute' => 'sku',
                'contentOptions' => ['class' => 'sku'],
            ],
            [
                'attribute' => 'chinese_name',
                'format' => 'raw',
                'value' => function ($model) {
                    $s = $model['chinese_name'];
                    if (!empty($model['image'])) {
                        $s = Html::a(Html::img($model['image'], ['style' => 'width: 20px; height: 20px; border-radius: 10px; margin-right: 10px;']), $model['image'], ['target' => '_blank', 'data-pjax' => 0]) . $s;
                    }

                    return $s;
                },
            ],
            [
                'attribute' => 'weight',
                'value' => function ($model) {
                    return $model->weight ?: '-';
                },
                'contentOptions' => ['class' => 'number'],
            ],
            [
                'attribute' => 'size_length',
                'value' => function ($model) {
                    return $model->size_length ?: '-';
                },
                'contentOptions' => ['class' => 'number'],
            ],
            [
                'attribute' => 'size_width',
                'value' => function ($model) {
                    return $model->size_width ?: '-';
                },
                'contentOptions' => ['class' => 'number'],
            ],
            [
                'attribute' => 'size_height',
                'value' => function ($model) {
                    return $model->size_height ?: '-';
                },
                'contentOptions' => ['class' => 'number'],
            ],
            [
                'attribute' => 'price',
                'value' => function ($model) {
                    return $model->price > 0 ? $model->price : '-';
                },
                'contentOptions' => ['class' => 'price'],
            ],
            [
                'attribute' => 'cost_price',
                'value' => function ($model) {
                    return $model->cost_price ?: '-';
                },
                'contentOptions' => ['class' => 'number'],
            ],
            [
                'attribute' => 'created_at',
                'format' => 'datetime',
                'contentOptions' => ['class' => 'datetime']
            ],
            [
                'attribute' => 'updated_at',
                'format' => 'datetime',
                'contentOptions' => ['class' => 'datetime']
            ],
            [
                'class' => 'yii\grid\ActionColumn',
                'template' => '{view} {update} {delete}',
                'headerOptions' => ['class' => 'buttons-3 last'],
            ],
        ],
    ]); ?>
    <?php Pjax::end(); ?>
</div>

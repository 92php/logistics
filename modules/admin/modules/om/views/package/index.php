<?php

use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel app\modules\admin\modules\om\models\PackageSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = '包裹列表';
$this->params['breadcrumbs'][] = $this->title;

$this->params['menus'] = [
    ['label' => Yii::t('app', 'List'), 'url' => ['index']],
    ['label' => Yii::t('app', 'Create'), 'url' => ['create']],
];
?>
<div class="package-index">
    <?= $this->render('_search', ['model' => $searchModel]); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'columns' => [
            [
                'class' => 'yii\grid\SerialColumn',
                'contentOptions' => ['class' => 'serial-number']
            ],

            [
                'attribute' => 'number',
                'value' => function ($model) {
                    return \yii\helpers\Html::a($model->number, ['view', 'id' => $model->id]);
                },
                'format' => 'raw',
            ],
            'title',
            [
                'attribute' => 'items_quantity',
                'contentOptions' => ['class' => 'number']
            ],
            [
                'attribute' => 'remaining_items_quantity',
                'contentOptions' => ['class' => 'number']
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
</div>

<?php

use yii\grid\GridView;
use yii\helpers\Html;
use yii\widgets\Pjax;

/* @var $this yii\web\View */
/* @var $searchModel app\modules\admin\modules\wuliu\models\FreightTemplateFeeSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = '运费模板计费管理';
$this->params['breadcrumbs'][] = $this->title;

$this->params['menus'] = [
    ['label' => Yii::t('app', 'List'), 'url' => ['index']],
    ['label' => Yii::t('app', 'Create'), 'url' => ['create']],
];
?>
<div class="freight-template-fee-index">
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
                'label' => '模板名称',
                'attribute' => 'template.name',
                'value' => function ($model) {
                    $template = $model->template;

                    return $template ? Html::a($template->name, ['freight-template/view', 'id' => $template->id]) : '';
                },
                'format' => 'raw'
            ],
            [
                'label' => '线路名称',
                'attribute' => 'line.name',
                'value' => function ($model) {
                    $line = $model->line;

                    return $line ? Html::a($line->name, ['company/view', 'id' => $line->id]) : '';
                },
                'format' => 'raw'
            ],
            [
                'label' => '重量范围',
                'value' => function ($model) {
                    return $model->min_weight . 'g - ' . $model->max_weight . 'g';
                },
                'contentOptions' => ['style' => 'width: 100px']
            ],
            [
                'label' => '首重/首重费用',
                'value' => function ($model) {
                    return $model->first_weight . 'g / ￥' . $model->first_fee;
                },
                'contentOptions' => ['style' => 'width: 100px']
            ],
            [
                'label' => '续重单位重量/单价',
                'value' => function ($model) {
                    return $model->continued_weight . 'g / ￥' . $model->continued_fee;
                },
                'contentOptions' => ['style' => 'width: 100px']
            ],
            [
                'attribute' => 'base_fee',
                'value' => function ($model) {
                    return '￥' . $model->base_fee;
                },
                'contentOptions' => ['class' => 'number']
            ],
            'remark:ntext',
            [
                'attribute' => 'enabled',
                'format' => 'boolean',
                'contentOptions' => ['class' => 'boolean']
            ],
            [
                'attribute' => 'created_at',
                'format' => 'datetime',
                'contentOptions' => ['class' => 'datetime'],
            ],
            [
                'attribute' => 'updated_at',
                'format' => 'datetime',
                'contentOptions' => ['class' => 'datetime'],
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

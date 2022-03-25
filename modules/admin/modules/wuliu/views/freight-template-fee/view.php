<?php

use yii\helpers\Html;
use yii\web\YiiAsset;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model app\modules\admin\modules\wuliu\models\FreightTemplateFee */

$template = $model->template;
$this->title = $template ? $template->name : '';
$this->params['breadcrumbs'][] = ['label' => '运费模板计费管理', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
YiiAsset::register($this);

$this->params['menus'] = [
    ['label' => Yii::t('app', 'List'), 'url' => ['index']],
    ['label' => Yii::t('app', 'Create'), 'url' => ['create']],
    ['label' => Yii::t('app', 'Update'), 'url' => ['update', 'id' => $model->id]]
];
?>
<div class="freight-template-fee-view">
    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'id',
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

                    return $line ? Html::a($line->name, ['company-line/view', 'id' => $line->id]) : '';
                },
                'format' => 'raw'
            ],
            [
                'label' => '重量范围',
                'value' => function ($model) {
                    return $model->min_weight . 'g - ' . $model->max_weight . 'g';
                },
            ],
            [
                'label' => '首重/首重费用',
                'value' => function ($model) {
                    return $model->first_weight . 'g / ￥' . $model->first_fee;
                },
            ],
            [
                'label' => '续重单位重量/单价',
                'value' => function ($model) {
                    return $model->continued_weight . 'g / ￥' . $model->continued_fee;
                },
            ],
            [
                'attribute' => 'base_fee',
                'value' => function ($model) {
                    return '￥' . $model->base_fee;
                },
            ],
            [
                'attribute' => 'enabled',
                'format' => 'boolean',
            ],
            'remark:ntext',
            [
                'attribute' => 'created_at',
                'format' => 'datetime',
            ],
            [
                'attribute' => 'updated_at',
                'format' => 'datetime',
            ],
        ],
    ]) ?>
</div>

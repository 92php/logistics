<?php

use app\modules\admin\modules\wuliu\extensions\Formatter;
use yii\grid\GridView;
use yii\widgets\Pjax;

/* @var $this yii\web\View */
/* @var $searchModel app\modules\admin\modules\wuliu\models\CompanyLineRouteSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = '物流线路路由管理';
$this->params['breadcrumbs'][] = $this->title;

$this->params['menus'] = [
    ['label' => Yii::t('app', 'List'), 'url' => ['index']],
    ['label' => Yii::t('app', 'Create'), 'url' => ['create']],
];
/* @var $formatter Formatter */
$formatter = Yii::$app->getFormatter();
?>
<div class="company-line-route-index">
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
                'attribute' => 'line.name',
                'contentOptions' => ['style' => 'width: 200px']
            ],
            [
                'attribute' => 'step',
                'contentOptions' => ['class' => 'number']
            ],
            [
                'attribute' => 'event',
                'contentOptions' => ['style' => 'width: 120px']
            ],
            'detection_keyword',
            [
                'attribute' => 'estimate_days',
                'value' => function ($model) {
                    return $model['estimate_days'] ? "{$model['estimate_days']} 天" : '自动估算';
                },
                'contentOptions' => ['class' => 'number']
            ],
            [
                'attribute' => 'package_status',
                'value' => function ($model) use ($formatter) {
                    return $model['package_status'] ? $formatter->asPackageStatus($model['package_status']) : null;
                },
                'contentOptions' => ['style' => 'width: 60px']
            ],
            [
                'attribute' => 'enabled',
                'format' => 'boolean',
                'contentOptions' => ['class' => 'boolean']
            ],
            //'created_at',
            //'created_by',
            //'updated_at',
            //'updated_by',
            [
                'class' => 'yii\grid\ActionColumn',
                'template' => '{view} {update} {delete}',
                'headerOptions' => ['class' => 'buttons-3 last'],
            ],
        ],
    ]); ?>
    <?php Pjax::end(); ?>
</div>

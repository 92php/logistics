<?php

use yii\grid\GridView;
use yii\helpers\Html;
use yii\widgets\Pjax;

/* @var $this yii\web\View */
/* @var $searchModel app\modules\admin\modules\wuliu\models\FreightTemplateSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = '运费模板管理';
$this->params['breadcrumbs'][] = $this->title;

$this->params['menus'] = [
    ['label' => Yii::t('app', 'List'), 'url' => ['index']],
    ['label' => Yii::t('app', 'Create'), 'url' => ['create']],
];
?>
<div class="freight-template-index">
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
                'attribute' => 'name',
                'contentOptions' => ['style' => 'width: 200px']
            ],
            [
                'label' => '物流公司名称',
                'attribute' => 'company.name',
                'value' => function ($model) {
                    $company = $model->company;

                    return $company ? Html::a($company->name, ['company/view', 'id' => $model->company_id]) : '';
                },
                'format' => 'raw',
                'contentOptions' => ['style' => 'width: 200px']
            ],
            [
                'attribute' => 'fee_mode',
                'format' => 'feeMode',
                'contentOptions' => ['style' => 'width: 100px']
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

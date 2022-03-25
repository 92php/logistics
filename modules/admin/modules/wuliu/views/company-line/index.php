<?php

use yii\grid\GridView;
use yii\helpers\Html;
use yii\widgets\Pjax;

/* @var $this yii\web\View */
/* @var $searchModel app\modules\admin\modules\wuliu\models\CompanyLineSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = '物流公司线路管理';
$this->params['breadcrumbs'][] = $this->title;

$this->params['menus'] = [
    ['label' => Yii::t('app', 'List'), 'url' => ['index']],
    ['label' => Yii::t('app', 'Create'), 'url' => ['create']],
];
?>
<div class="company-line-index">
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
                'attribute' => 'company.name',
                'value' => function ($model) {
                    $company = $model->company;

                    return $company ? Html::a($company->name, ['company/view', 'id' => $model->company_id]) : '';
                },
                'format' => 'raw',
                'contentOptions' => ['style' => 'width: 160px']
            ],
            [
                'attribute' => 'name',
                'value' => function ($model) {
                    return "[ {$model['id']} ] {$model['name']}";
                },
                'contentOptions' => ['style' => 'width: 160px']
            ],
            [
                'attribute' => 'estimate_days',
                'contentOptions' => ['class' => 'number']
            ],
            [
                'attribute' => 'enabled',
                'contentOptions' => ['class' => 'boolean'],
                'format' => 'boolean'
            ],
            'remark:ntext',
            [
                'class' => 'yii\grid\ActionColumn',
                'template' => '{view} {update} {delete}',
                'headerOptions' => ['class' => 'buttons-3 last'],
            ],
        ],
    ]); ?>
    <?php Pjax::end(); ?>
</div>

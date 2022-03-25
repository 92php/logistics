<?php

use yii\grid\GridView;
use yii\helpers\Html;
use yii\widgets\Pjax;

/* @var $this yii\web\View */
/* @var $searchModel app\modules\admin\modules\wuliu\models\DxmAccountSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = '店小秘账户列表';
$this->params['breadcrumbs'][] = $this->title;

$this->params['menus'] = [
    ['label' => Yii::t('app', 'List'), 'url' => ['index']],
    ['label' => Yii::t('app', 'Create'), 'url' => ['create']],
];
?>
<div class="dxm-account-index">
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
                'attribute' => 'username',
                'contentOptions' => ['class' => 'username']
            ],
            [
                'attribute' => 'company_id',
                'value' => function ($model) {
                    $company = $model->company;

                    return $company ? Html::a($company->name, ['company/view', 'id' => $model->company_id]) : '';
                },
                'format' => 'raw',
                'contentOptions' => ['style' => 'width: 200px;']
            ],
            [
                'attribute' => 'platform_id',
                'format' => 'platform',
                'contentOptions' => ['style' => 'width: 150px;']
            ],
            'remark:ntext',
            [
                'attribute' => 'is_valid',
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

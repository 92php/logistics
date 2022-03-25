<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;

/* @var $this yii\web\View */
/* @var $searchModel app\modules\admin\modules\g\models\CustomerSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = '客户管理';
$this->params['breadcrumbs'][] = $this->title;

$this->params['menus'] = [
    ['label' => Yii::t('app', 'List'), 'url' => ['index']],
    ['label' => Yii::t('app', 'Create'), 'url' => ['create']],
];
?>
<div class="customer-index">
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
                'attribute' => 'platform_id',
                'format' => 'platform',
                'contentOptions' => ['class' => 'platform']
            ],
            'key',
            [
                'attribute' => 'full_name',
                'contentOptions' => ['class' => 'full-name']
            ],
            [
                'attribute' => 'email',
                'contentOptions' => ['class' => 'email']
            ],
            'phone',
            [
                'attribute' => 'currency',
                'contentOptions' => ['class' => 'currency']
            ],
            'remark:ntext',
            [
                'attribute' => 'status',
                'format' => 'customerStatus',
                'contentOptions' => ['style' => 'width: 60px; text-align: center;']
            ],
            [
                'attribute' => 'created_at',
                'format' => 'datetime',
                'contentOptions' => ['class' => 'datetime'],
            ],
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

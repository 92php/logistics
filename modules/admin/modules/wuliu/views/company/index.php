<?php

use yii\grid\GridView;
use yii\helpers\Html;
use yii\widgets\Pjax;

/* @var $this yii\web\View */
/* @var $searchModel app\modules\admin\modules\wuliu\models\CompanySearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = '物流公司列表';
$this->params['breadcrumbs'][] = $this->title;

$this->params['menus'] = [
    ['label' => Yii::t('app', 'List'), 'url' => ['index']],
    ['label' => Yii::t('app', 'Create'), 'url' => ['create']],
];
?>
<div class="company-index">
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
                'attribute' => 'code',
                'format' => 'raw',
                'contentOptions' => ['style' => 'width: 120px'],
                'value' => function ($model) {
                    return Html::a($model['code'], ['view', 'id' => $model->id]);
                },
            ],
            [
                'attribute' => 'name',
                'contentOptions' => ['style' => 'width: 160px']
            ],
            [
                'attribute' => 'website_url',
                'format' => 'url',
                'contentOptions' => ['style' => 'width: 200px']
            ],
            [
                'attribute' => 'linkman',
                'contentOptions' => ['class' => 'username']
            ],
            [
                'attribute' => 'enabled',
                'contentOptions' => ['class' => 'boolean'],
                'format' => 'boolean'
            ],
            [
                'attribute' => 'mobile_phone',
                'contentOptions' => ['class' => 'mobile-phone']
            ],
            'remark:ntext',
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

<?php

use yii\grid\GridView;
use yii\widgets\Pjax;

/* @var $this yii\web\View */
/* @var $searchModel app\modules\admin\modules\g\models\PackageSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = '包裹管理';
$this->params['breadcrumbs'][] = $this->title;

$this->params['menus'] = [
    ['label' => Yii::t('app', 'List'), 'url' => ['index']],
    ['label' => Yii::t('app', 'Create'), 'url' => ['create']],
    ['label' => Yii::t('app', '导入发货数据'), 'url' => ['import-delivery-data']],
];
?>
<div class="package-index">
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
                'attribute' => 'country.abbreviation',
                'contentOptions' => ['style' => 'width: 40px; text-align: center'],
            ],
            [
                'attribute' => 'shop.name',
                'contentOptions' => ['style' => 'width: 120px;'],
            ],
            [
                'attribute' => 'number',
                'contentOptions' => ['class' => 'package-number'],
            ],
            [
                'attribute' => 'waybill_number',
                'contentOptions' => ['class' => 'waybill-number'],
            ],
            [
                'attribute' => 'weight',
                'contentOptions' => ['class' => 'number']
            ],
            [
                'attribute' => 'freight_cost',
                'contentOptions' => ['class' => 'number']
            ],
            [
                'attribute' => 'delivery_datetime',
                'format' => 'datetime',
                'contentOptions' => ['class' => 'datetime']
            ],
            //'logistics_line_id',
            //'logistics_query_raw_results:ntext',
            [
                'attribute' => 'logistics_last_check_datetime',
                'format' => 'datetime',
                'contentOptions' => ['class' => 'datetime']
            ],
            //'status',
            'remark:ntext',
            //'created_at',
            //'created_by',
            //'updated_at',
            //'updated_by',
            [
                'class' => 'yii\grid\ActionColumn',
                'template' => '{view} {update}',
                'headerOptions' => ['class' => 'buttons-2 last'],
            ],
        ],
    ]); ?>
    <?php Pjax::end(); ?>
</div>

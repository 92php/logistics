<?php

use yii\grid\GridView;
use yii\helpers\Html;
use yii\widgets\Pjax;

/* @var $this yii\web\View */
/* @var $searchModel app\modules\admin\modules\wuliu\models\PackageSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = '包裹列表';
$this->params['breadcrumbs'][] = $this->title;

$this->params['menus'] = [
    ['label' => Yii::t('app', 'List'), 'url' => ['index']],
];
?>
<div class="package-index">
    <?php Pjax::begin(); ?>
    <?= $this->render('_search', ['model' => $searchModel]); ?>
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'columns' => [
            [
                'attribute' => 'id',
                'header' => '#',
                'contentOptions' => ['class' => 'serial-number']
            ],
            [
                'attribute' => 'package_id',
                'format' => 'raw',
                'value' => function ($model) {
                    return Html::a($model['package_id'], ['view', 'id' => $model['id']]);
                },
                'contentOptions' => ['style' => 'width: 120px; text-align: center;'],
            ],
            [
                'attribute' => 'package_number',
                'contentOptions' => ['style' => 'width: 120px; text-align: center;'],
            ],
            [
                'attribute' => 'order_number',
                'format' => 'raw',
                'value' => function ($model) {
                    return Html::tag('span', $model['order_number'], ['style' => 'display: block; width: 160px; overflow:hidden; text-overflow: ellipsis; word-wrap: break-word']);
                },
                'contentOptions' => ['style' => 'width: 160px;'],
            ],
            [
                'attribute' => 'waybill_number',
                'contentOptions' => ['style' => 'width: 140px;'],
            ],
            [
                'attribute' => 'line.name',
                'contentOptions' => ['style' => 'width: 160px;'],
            ],
            [
                'attribute' => 'country.chinese_name',
                'contentOptions' => ['style' => 'width: 40px;', 'class' => 'center'],
            ],
            [
                'attribute' => 'weight',
                'contentOptions' => ['class' => 'number'],
            ],
            [
                'attribute' => 'freight_cost',
                'contentOptions' => ['class' => 'number'],
            ],
            [
                'attribute' => 'freightCostEstimate',
                'header' => '运费估算',
                'contentOptions' => ['class' => 'number'],
            ],
            [
                'attribute' => 'dxmAccount.username',
                'contentOptions' => ['class' => 'username'],
            ],
            [
                'attribute' => 'shop_name',
                'contentOptions' => ['style' => 'width: 100px;'],
            ],
            [
                'attribute' => 'delivery_datetime',
                'format' => 'datetime',
                'contentOptions' => ['class' => 'datetime'],
            ],
            [
                'attribute' => 'estimate_days',
                'contentOptions' => ['class' => 'number'],
            ],
            [
                'attribute' => 'final_days',
                'contentOptions' => ['class' => 'number'],
            ],
            [
                'attribute' => 'sync_status',
                'format' => 'packageSyncStatus',
                'contentOptions' => ['class' => 'status-text'],
            ],
            [
                'attribute' => 'status',
                'format' => 'packageStatus',
                'contentOptions' => ['style' => 'width: 60px;'],
            ],
            [
                'attribute' => 'remark',
                'format' => 'ntext',
                'headerOptions' => ['class' => 'last'],
            ],
        ],
    ]); ?>
    <?php Pjax::end(); ?>
</div>

<?php

use app\modules\admin\modules\g\extensions\Formatter;
use app\modules\admin\modules\g\models\SyncTask;
use yii\grid\GridView;
use yii\widgets\Pjax;

/* @var $this yii\web\View */
/* @var $searchModel app\modules\admin\modules\g\models\SyncTaskSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = '同步任务管理';
$this->params['breadcrumbs'][] = $this->title;

$this->params['menus'] = [
    ['label' => Yii::t('app', 'List'), 'url' => ['index']],
    ['label' => Yii::t('app', 'Create'), 'url' => ['create']],
];

/* @var $formatter Formatter */
$formatter = Yii::$app->getFormatter();
?>
<div class="sync-task-index">
    <?php Pjax::begin([
        'timeout' => 6000,
    ]); ?>
    <?= $this->render('_search', ['model' => $searchModel]); ?>
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'columns' => [
            [
                'class' => 'yii\grid\SerialColumn',
                'contentOptions' => ['class' => 'serial-number']
            ],
            [
                'attribute' => 'shop.name',
                'format' => 'raw',
                'value' => function ($model) use ($formatter) {
                    $s = sprintf("[ %s ] #%d %s", $formatter->asOrganization($model['shop']['organization_id']), $model['shop_id'], $model['shop']['name']);
                    if ($model['status'] == SyncTask::STATUS_WORKING) {
                        $s .= "<span style='color: green; font-weight: bold'>（Working）</span>";
                    }

                    return $s;
                },
            ],
            [
                'attribute' => 'begin_date',
                'format' => 'date',
                'contentOptions' => ['class' => 'date'],
            ],
            [
                'attribute' => 'end_date',
                'format' => 'date',
                'contentOptions' => ['class' => 'date'],
            ],
            [
                'attribute' => 'priority',
                'contentOptions' => ['class' => 'number'],
            ],
            [
                'attribute' => 'start_datetime',
                'format' => 'datetime',
                'contentOptions' => ['class' => 'datetime'],
            ],
            [
                'attribute' => 'status',
                'value' => function ($model) {
                    return $model['status'] == SyncTask::STATUS_PENDING ? '待处理' : '处理中';
                },
                'contentOptions' => ['style' => 'width: 50px; text-align: center'],
            ],
            [
                'class' => 'yii\grid\ActionColumn',
                'template' => '{update} {delete}',
                'headerOptions' => ['class' => 'buttons-2 last'],
            ],
        ],
    ]); ?>
    <?php Pjax::end(); ?>
</div>

<?php

use app\modules\admin\modules\wuliu\extensions\Formatter;
use yii\web\YiiAsset;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model app\modules\admin\modules\wuliu\models\CompanyLineRoute */

$line = $model->line;
$this->title = '更新: ' . ($line ? $line->name : '未知线路') . '第' . $model->step . '个监控点';
$this->params['breadcrumbs'][] = ['label' => '物流线路路由管理', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
YiiAsset::register($this);

$this->params['menus'] = [
    ['label' => Yii::t('app', 'List'), 'url' => ['index']],
    ['label' => Yii::t('app', 'Create'), 'url' => ['create']],
    ['label' => Yii::t('app', 'Update'), 'url' => ['update', 'id' => $model->id]]
];
?>
<div class="company-line-route-view">
    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'id',
            'line.name',
            'step',
            'event',
            'detection_keyword',
            'estimate_days',
            [
                'attribute' => 'package_status',
                'value' => function ($model) {
                    /* @var $formatter Formatter */
                    $formatter = Yii::$app->getFormatter();

                    return $model['package_status'] ? $formatter->asPackageStatus($model['package_status']) : null;
                },
            ],
            'enabled:boolean',
            'created_at:datetime',
            'updated_at:datetime',
        ],
    ]) ?>
</div>

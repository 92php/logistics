<?php

/* @var $this yii\web\View */
/* @var $model app\modules\admin\modules\wuliu\models\CompanyLineRoute */

$line = $model->line;
$this->title = '更新: ' . ($line ? $line->name : '未知线路') . '第' . $model->step . '个监控点';
$this->params['breadcrumbs'][] = ['label' => '物流线路路由管理', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => ($line ? $line->name : '未知线路') . '第' . $model->step . '个监控点', 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = '更新';

$this->params['menus'] = [
    ['label' => Yii::t('app', 'List'), 'url' => ['index']],
    ['label' => Yii::t('app', 'Create'), 'url' => ['create']]
];
?>
<div class="company-line-route-update">
    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>
</div>

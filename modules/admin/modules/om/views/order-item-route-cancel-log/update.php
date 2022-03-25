<?php

/* @var $this yii\web\View */
/* @var $model app\modules\admin\modules\om\models\OrderItemRouteCancelLog */

$this->title = 'Update Cancel Log: ' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Cancel Logs', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';

$this->params['menus'] = [
    ['label' => Yii::t('app', 'List'), 'url' => ['index']],
    ['label' => Yii::t('app', 'Create'), 'url' => ['create']]
];
?>
<div class="cancel-log-update">
    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>
</div>

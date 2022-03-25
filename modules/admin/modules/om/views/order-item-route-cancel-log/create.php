<?php

/* @var $this yii\web\View */
/* @var $model app\modules\admin\modules\om\models\OrderItemRouteCancelLog */

$this->title = 'Create Cancel Log';
$this->params['breadcrumbs'][] = ['label' => 'Cancel Logs', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

$this->params['menus'] = [
    ['label' => Yii::t('app', 'List'), 'url' => ['index']],
    ['label' => Yii::t('app', 'Create'), 'url' => ['create']],
];
?>
<div class="cancel-log-create">
    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>
</div>

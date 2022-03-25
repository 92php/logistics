<?php

/* @var $this yii\web\View */
/* @var $model app\modules\admin\modules\g\models\Customer */

$this->title = '更新: ' . $model->full_name;
$this->params['breadcrumbs'][] = ['label' => '客户管理', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->full_name, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = '更新';

$this->params['menus'] = [
    ['label' => Yii::t('app', 'List'), 'url' => ['index']],
    ['label' => Yii::t('app', 'Create'), 'url' => ['create']]
];
?>
<div class="customer-update">
    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>
</div>

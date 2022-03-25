<?php

/* @var $this yii\web\View */
/* @var $model app\modules\admin\modules\g\models\Vendor */

$this->title = '更新: ' . $model->name;
$this->params['breadcrumbs'][] = ['label' => '供应商管理', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->name, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = '更新';

$this->params['menus'] = [
    ['label' => Yii::t('app', 'List'), 'url' => ['index']],
    ['label' => Yii::t('app', 'Create'), 'url' => ['create']]
];
?>
<div class="vendor-update">
    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>
</div>

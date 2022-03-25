<?php

/* @var $this yii\web\View */
/* @var $model app\modules\admin\modules\g\models\WarehouseSheet */

$this->title = 'Update Warehouse Sheet: ' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Warehouse Sheets', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';

$this->params['menus'] = [
    ['label' => Yii::t('app', 'List'), 'url' => ['index']],
    ['label' => Yii::t('app', 'Create'), 'url' => ['create']]
];
?>
<div class="warehouse-sheet-update">
    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>
</div>

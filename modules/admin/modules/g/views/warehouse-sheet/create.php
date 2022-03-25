<?php

/* @var $this yii\web\View */
/* @var $model app\modules\admin\modules\g\models\WarehouseSheet */

$this->title = 'Create Warehouse Sheet';
$this->params['breadcrumbs'][] = ['label' => 'Warehouse Sheets', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

$this->params['menus'] = [
    ['label' => Yii::t('app', 'List'), 'url' => ['index']],
    ['label' => Yii::t('app', 'Create'), 'url' => ['create']],
];
?>
<div class="warehouse-sheet-create">
    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>
</div>

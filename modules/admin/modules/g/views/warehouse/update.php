<?php

/* @var $this yii\web\View */
/* @var $model app\modules\admin\modules\g\models\Warehouse */

$this->title = '更新: ' . $model->name;
$this->params['breadcrumbs'][] = ['label' => '仓库管理', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->name, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';

$this->params['menus'] = [
    ['label' => Yii::t('app', 'List'), 'url' => ['index']],
    ['label' => Yii::t('app', 'Create'), 'url' => ['create']]
];
?>
<div class="warehouse-update">
    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>
</div>

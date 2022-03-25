<?php

/* @var $this yii\web\View */
/* @var $model app\modules\admin\modules\g\models\Package */

$this->title = '更新: ' . $model->number;
$this->params['breadcrumbs'][] = ['label' => '包裹管理', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->number, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = '更新';

$this->params['menus'] = [
    ['label' => Yii::t('app', 'List'), 'url' => ['index']],
    ['label' => Yii::t('app', 'Create'), 'url' => ['create']]
];
?>
<div class="package-update">
    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>
</div>

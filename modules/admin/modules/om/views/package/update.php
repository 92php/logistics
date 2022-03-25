<?php

/* @var $this yii\web\View */
/* @var $model app\modules\admin\modules\om\models\Package */

$this->title = '更新包裹: ' . $model->number;
$this->params['breadcrumbs'][] = ['label' => '包裹列表', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->title, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = '修改';

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

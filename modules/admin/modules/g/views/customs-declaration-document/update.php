<?php

/* @var $this yii\web\View */
/* @var $model app\modules\admin\modules\g\models\CustomsDeclarationDocument */

$this->title = '更新: ' . $model->id;
$this->params['breadcrumbs'][] = ['label' => '报关信息', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';

$this->params['menus'] = [
    ['label' => Yii::t('app', 'List'), 'url' => ['index']],
    ['label' => Yii::t('app', 'Create'), 'url' => ['create']]
];
?>
<div class="customs-declaration-document-update">
    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>
</div>

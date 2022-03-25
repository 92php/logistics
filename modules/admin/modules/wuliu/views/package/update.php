<?php

/* @var $this yii\web\View */
/* @var $model app\modules\admin\modules\wuliu\models\Package */

$this->title = 'Update Package: ' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Packages', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';

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

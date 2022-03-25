<?php

/* @var $this yii\web\View */
/* @var $model app\modules\admin\modules\wuliu\models\Company */

$this->title = '修改物流公司: ' . $model->name;
$this->params['breadcrumbs'][] = ['label' => '物流公司列表', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->name, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = '修改';

$this->params['menus'] = [
    ['label' => Yii::t('app', 'List'), 'url' => ['index']],
    ['label' => Yii::t('app', 'Create'), 'url' => ['create']]
];
?>
<div class="company-update">
    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>
</div>

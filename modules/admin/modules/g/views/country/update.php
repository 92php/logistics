<?php

/* @var $this yii\web\View */
/* @var $model app\modules\admin\modules\g\models\Country */

$this->title = '更新: ' . $model->chinese_name;
$this->params['breadcrumbs'][] = ['label' => '国家管理', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->chinese_name, 'url' => ['update', 'id' => $model->id]];
$this->params['breadcrumbs'][] = '更新';

$this->params['menus'] = [
    ['label' => Yii::t('app', 'List'), 'url' => ['index']],
    ['label' => Yii::t('app', 'Create'), 'url' => ['create']]
];
?>
<div class="country-update">
    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>
</div>

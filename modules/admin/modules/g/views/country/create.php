<?php

/* @var $this yii\web\View */
/* @var $model app\modules\admin\modules\g\models\Country */

$this->title = '添加';
$this->params['breadcrumbs'][] = ['label' => '国家管理', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

$this->params['menus'] = [
    ['label' => Yii::t('app', 'List'), 'url' => ['index']],
    ['label' => Yii::t('app', 'Create'), 'url' => ['create']],
];
?>
<div class="country-create">
    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>
</div>

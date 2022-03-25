<?php

/* @var $this yii\web\View */
/* @var $model app\modules\admin\modules\g\models\Package */

$this->title = '添加';
$this->params['breadcrumbs'][] = ['label' => '包裹管理', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

$this->params['menus'] = [
    ['label' => Yii::t('app', 'List'), 'url' => ['index']],
    ['label' => Yii::t('app', 'Create'), 'url' => ['create']],
];
?>
<div class="package-create">
    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>
</div>

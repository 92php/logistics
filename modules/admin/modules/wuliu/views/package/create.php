<?php

/* @var $this yii\web\View */
/* @var $model app\modules\admin\modules\wuliu\models\Package */

$this->title = 'Create Package';
$this->params['breadcrumbs'][] = ['label' => 'Packages', 'url' => ['index']];
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

<?php

/* @var $this yii\web\View */
/* @var $model app\modules\admin\modules\om\models\Part */

$this->title = '创建配件';
$this->params['breadcrumbs'][] = ['label' => '配件管理', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
$this->params['menus'] = [
    ['label' => Yii::t('app', 'List'), 'url' => ['index']],
    ['label' => Yii::t('app', 'Create'), 'url' => ['create']],
];
?>
<div class="part-create">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>

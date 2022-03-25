<?php

/* @var $this yii\web\View */
/* @var $model app\modules\admin\modules\g\models\ProductCombine */

$this->title = 'Create Product Combine';
$this->params['breadcrumbs'][] = ['label' => 'Product Combines', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

$this->params['menus'] = [
    ['label' => Yii::t('app', 'List'), 'url' => ['index']],
    ['label' => Yii::t('app', 'Create'), 'url' => ['create']],
];
?>
<div class="product-combine-create">
    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>
</div>

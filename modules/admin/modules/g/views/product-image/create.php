<?php

/* @var $this yii\web\View */
/* @var $model app\modules\admin\modules\g\models\ProductImage */

$this->title = 'Create Product Image';
$this->params['breadcrumbs'][] = ['label' => 'Product Images', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

$this->params['menus'] = [
    ['label' => Yii::t('app', 'List'), 'url' => ['index']],
    ['label' => Yii::t('app', 'Create'), 'url' => ['create']],
];
?>
<div class="product-image-create">
    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>
</div>

<?php

/* @var $this yii\web\View */
/* @var $model app\modules\admin\modules\g\models\ProductImage */

$this->title = 'Update Product Image: ' . $model->title;
$this->params['breadcrumbs'][] = ['label' => 'Product Images', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->title, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';

$this->params['menus'] = [
    ['label' => Yii::t('app', 'List'), 'url' => ['index']],
    ['label' => Yii::t('app', 'Create'), 'url' => ['create']]
];
?>
<div class="product-image-update">
    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>
</div>

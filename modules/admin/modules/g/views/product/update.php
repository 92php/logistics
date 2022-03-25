<?php

/* @var $this yii\web\View */
/* @var $model app\modules\admin\modules\g\models\Product */

$this->title = '更新: ' . $model->sku;
$this->params['breadcrumbs'][] = ['label' => '商品管理', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->sku, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = '更新';

$this->params['menus'] = [
    ['label' => Yii::t('app', 'List'), 'url' => ['index']],
    ['label' => Yii::t('app', 'Create'), 'url' => ['create']]
];
?>
<div class="product-update">
    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>
</div>

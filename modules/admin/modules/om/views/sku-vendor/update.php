<?php

/* @var $this yii\web\View */
/* @var $model app\modules\admin\modules\om\models\SkuVendor */

$this->title = '更新: ' . $model->sku;
$this->params['breadcrumbs'][] = ['label' => 'SKU 供应商匹配管理', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = '更新';

$this->params['menus'] = [
    ['label' => Yii::t('app', 'List'), 'url' => ['index']],
    ['label' => Yii::t('app', 'Create'), 'url' => ['create']]
];
?>
<div class="sku-vendor-update">
    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>
</div>

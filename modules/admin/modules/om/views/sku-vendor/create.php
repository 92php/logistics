<?php

/* @var $this yii\web\View */
/* @var $model app\modules\admin\modules\om\models\SkuVendor */

$this->title = '添加';
$this->params['breadcrumbs'][] = ['label' => 'SKU 供应商匹配管理', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

$this->params['menus'] = [
    ['label' => Yii::t('app', 'List'), 'url' => ['index']],
    ['label' => Yii::t('app', 'Create'), 'url' => ['create']],
];
?>
<div class="sku-vendor-create">
    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>
</div>

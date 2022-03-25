<?php

/* @var $this yii\web\View */
/* @var $model app\modules\admin\modules\om\models\OrderItemRoute */

$this->title = '添加商品路由';
$this->params['breadcrumbs'][] = ['label' => '商品路由列表', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

$this->params['menus'] = [
    ['label' => Yii::t('app', 'List'), 'url' => ['index']],
    ['label' => Yii::t('app', 'Create'), 'url' => ['create']],
];
?>
<div class="order-route-create">
    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>
</div>

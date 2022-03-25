<?php

/* @var $this yii\web\View */
/* @var $model app\modules\admin\modules\om\models\OrderItemRoute */

$this->title = '修改商品路由: ' . $model->id;
$this->params['breadcrumbs'][] = ['label' => '商品路由列表', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = '修改';

$this->params['menus'] = [
    ['label' => Yii::t('app', 'List'), 'url' => ['index']],
    ['label' => Yii::t('app', 'Create'), 'url' => ['create']]
];
?>
<div class="order-route-update">
    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>
</div>

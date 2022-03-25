<?php

/* @var $this yii\web\View */
/* @var $model app\modules\admin\modules\g\models\Order */

$this->title = '修改订单: ' . $model->number;
$this->params['breadcrumbs'][] = ['label' => '订单列表', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->number, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = '修改';

$this->params['menus'] = [
    ['label' => Yii::t('app', 'List'), 'url' => ['index']],
];
?>
<div class="order-update">
    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>
</div>

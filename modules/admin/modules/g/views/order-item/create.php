<?php

/* @var $this yii\web\View */
/* @var $model app\modules\admin\modules\g\models\OrderItem */

$this->title = 'Create Order Item';
$this->params['breadcrumbs'][] = ['label' => 'Order Items', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

$this->params['menus'] = [
    ['label' => Yii::t('app', 'List'), 'url' => ['index']],
    ['label' => Yii::t('app', 'Create'), 'url' => ['create']],
];
?>
<div class="order-item-create">
    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>
</div>

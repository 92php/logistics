<?php

/* @var $this yii\web\View */
/* @var $model app\modules\admin\modules\g\models\Order */

$this->title = 'Create Order';
$this->params['breadcrumbs'][] = ['label' => 'Orders', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

$this->params['menus'] = [
    ['label' => Yii::t('app', 'List'), 'url' => ['index']],
    ['label' => Yii::t('app', 'Create'), 'url' => ['create']],
];
?>
<div class="order-create">
    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>
</div>

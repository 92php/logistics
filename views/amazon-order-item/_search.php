<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\modules\admin\modules\wuliu\models\AmazonOrderItemSearch */
/* @var $form yii\widgets\ActiveForm */
?>
<div class="form-outside">
    <div class="amazon-order-item-search form">
        <?php $form = ActiveForm::begin([
            'action' => ['index'],
            'method' => 'get',
        ]); ?>
        <div class="entry">
            <?= $form->field($model, 'order_id') ?>

            <?= $form->field($model, 'product_name') ?>
        </div>
        <div class="entry">
            <?= $form->field($model, 'size') ?>
            <?= $form->field($model, 'color') ?>
            <?php // echo $form->field($model, 'customized') ?>
        </div>
        <div class="form-group buttons">
            <?= Html::submitButton('搜索', ['class' => 'btn btn-primary']) ?>
            <?= Html::resetButton('重置', ['class' => 'btn btn-outline-secondary']) ?>
        </div>
        <?php ActiveForm::end(); ?>
    </div>
</div>
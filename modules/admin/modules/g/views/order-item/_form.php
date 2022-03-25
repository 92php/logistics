<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\modules\admin\modules\g\models\OrderItem */
/* @var $form yii\widgets\ActiveForm */
?>
<div class="form-outside">
    <div class="order-item-form form">
        <?php $form = ActiveForm::begin(); ?>

        <?= $form->field($model, 'order_id')->textInput() ?>

        <?= $form->field($model, 'product_id')->textInput() ?>

        <?= $form->field($model, 'sku')->textInput(['maxlength' => true]) ?>

        <?= $form->field($model, 'product_name')->textInput(['maxlength' => true]) ?>

        <?= $form->field($model, 'extend')->textInput() ?>

        <?= $form->field($model, 'quantity')->textInput() ?>

        <?= $form->field($model, 'vendor_id')->textInput() ?>

        <?= $form->field($model, 'sale_price')->textInput(['maxlength' => true]) ?>

        <?= $form->field($model, 'cost_price')->textInput(['maxlength' => true]) ?>

        <?= $form->field($model, 'remark')->textarea(['rows' => 6]) ?>
        <div class="form-group buttons">
            <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
        </div>
        <?php ActiveForm::end(); ?>
    </div>
</div>

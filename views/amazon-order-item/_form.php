<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\modules\admin\modules\wuliu\models\AmazonOrderItem */
/* @var $form yii\widgets\ActiveForm */
?>
<div class="amazon-order-item-form">
    <?php $form = ActiveForm::begin(); ?>


    <?= $form->field($model, 'order_id')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'product_name')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'product_image')->fileInput() ?>

    <?= $form->field($model, 'product_quantity')->textInput() ?>

    <?= $form->field($model, 'size')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'color')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'customized')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'remark')->textInput(['maxlength' => true]) ?>
    <div class="form-group buttons">
        <?= Html::submitButton('保存', ['class' => 'btn btn-success']) ?>
        <?= Html::a(Html::button('返回列表', ['class' => 'btn btn-default']), 'index') ?>
    </div>
    <?php ActiveForm::end(); ?>
</div>

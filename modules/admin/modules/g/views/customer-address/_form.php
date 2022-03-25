<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\modules\admin\modules\g\models\CustomerAddress */
/* @var $form yii\widgets\ActiveForm */
?>
<div class="form-outside">
    <div class="customer-address-form form">
        <?php $form = ActiveForm::begin(); ?>

        <?= $form->field($model, 'customer_id')->textInput() ?>

        <?= $form->field($model, 'key')->textInput(['maxlength' => true]) ?>

        <?= $form->field($model, 'first_name')->textInput(['maxlength' => true]) ?>

        <?= $form->field($model, 'last_name')->textInput(['maxlength' => true]) ?>

        <?= $form->field($model, 'company')->textInput(['maxlength' => true]) ?>

        <?= $form->field($model, 'address1')->textInput(['maxlength' => true]) ?>

        <?= $form->field($model, 'address2')->textInput(['maxlength' => true]) ?>

        <?= $form->field($model, 'country_id')->textInput() ?>

        <?= $form->field($model, 'province')->textInput(['maxlength' => true]) ?>

        <?= $form->field($model, 'city')->textInput(['maxlength' => true]) ?>

        <?= $form->field($model, 'zip')->textInput(['maxlength' => true]) ?>

        <?= $form->field($model, 'phone')->textInput(['maxlength' => true]) ?>
        <div class="form-group buttons">
            <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
        </div>
        <?php ActiveForm::end(); ?>
    </div>
</div>

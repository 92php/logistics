<?php

use app\models\Option;
use app\modules\admin\modules\g\models\Customer;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\modules\admin\modules\g\models\Customer */
/* @var $form yii\widgets\ActiveForm */
?>
<div class="form-outside">
    <div class="customer-form form">
        <?php $form = ActiveForm::begin(); ?>

        <?= $form->field($model, 'platform_id')->dropDownList(Option::platforms(), ['prompt' => '']) ?>

        <?= $form->field($model, 'key')->textInput(['maxlength' => true]) ?>

        <?= $form->field($model, 'email')->textInput(['maxlength' => true]) ?>

        <?= $form->field($model, 'first_name')->textInput(['maxlength' => true]) ?>

        <?= $form->field($model, 'last_name')->textInput(['maxlength' => true]) ?>

        <?= $form->field($model, 'phone')->textInput(['maxlength' => true]) ?>

        <?= $form->field($model, 'currency')->textInput(['maxlength' => true]) ?>

        <?= $form->field($model, 'remark')->textarea(['rows' => 6]) ?>

        <?= $form->field($model, 'status')->dropDownList(Customer::statusOptions()) ?>
        <div class="form-group buttons">
            <?= Html::submitButton('保存', ['class' => 'btn btn-success']) ?>
        </div>
        <?php ActiveForm::end(); ?>
    </div>
</div>

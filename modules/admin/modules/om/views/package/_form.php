<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\modules\admin\modules\om\models\Package */
/* @var $form yii\widgets\ActiveForm */
?>
<div class="form-outside">
    <div class="package-form form">
        <?php $form = ActiveForm::begin(); ?>

        <?= $form->field($model, 'number')->textInput() ?>

        <?= $form->field($model, 'title')->textInput(['maxlength' => true]) ?>

        <?= $form->field($model, 'items_quantity')->textInput(['type' => 'number']) ?>

        <?= $form->field($model, 'remaining_items_quantity')->textInput(['type' => 'number']) ?>
        <div class="form-group buttons">
            <?= Html::submitButton('保存', ['class' => 'btn btn-success']) ?>
        </div>
        <?php ActiveForm::end(); ?>
    </div>
</div>

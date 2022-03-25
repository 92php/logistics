<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\modules\admin\modules\om\models\Part */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="form-outside">
    <div class="cancel-log-form form">

        <?php $form = ActiveForm::begin(); ?>

        <?= $form->field($model, 'sku')->textInput(['maxlength' => true]) ?>

        <?= $form->field($model, 'customized')->textInput(['maxlength' => true]) ?>

        <?= $form->field($model, 'is_empty')->checkbox() ?>

        <div class="form-group buttons">
            <?= Html::submitButton('保存', ['class' => 'btn btn-success']) ?>
        </div>

        <?php ActiveForm::end(); ?>
    </div>
</div>

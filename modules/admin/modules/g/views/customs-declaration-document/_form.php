<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\modules\admin\modules\g\models\CustomsDeclarationDocument */
/* @var $form yii\widgets\ActiveForm */
?>
<div class="form-outside">
    <div class="customs-declaration-document-form form">
        <?php $form = ActiveForm::begin(); ?>

        <?= $form->field($model, 'code')->textInput(['maxlength' => true]) ?>

        <?= $form->field($model, 'chinese_name')->textInput(['maxlength' => true]) ?>

        <?= $form->field($model, 'english_name')->textInput(['maxlength' => true]) ?>

        <?= $form->field($model, 'weight')->textInput(['type' => 'number']) ?>

        <?= $form->field($model, 'amount')->textInput(['maxlength' => true]) ?>

        <?= $form->field($model, 'danger_level')->textInput() ?>

        <?= $form->field($model, 'default')->checkbox(null, false) ?>

        <?= $form->field($model, 'enabled')->textInput()->checkbox(null, false) ?>
        <div class="form-group buttons">
            <?= Html::submitButton('保存', ['class' => 'btn btn-success']) ?>
        </div>
        <?php ActiveForm::end(); ?>
    </div>
</div>

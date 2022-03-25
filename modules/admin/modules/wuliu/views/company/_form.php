<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\modules\admin\modules\wuliu\models\Company */
/* @var $form yii\widgets\ActiveForm */
?>
<div class="form-outside">
    <div class="company-form form">
        <?php $form = ActiveForm::begin(); ?>

        <?= $form->field($model, 'code')->textInput(['maxlength' => true]) ?>

        <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>

        <?= $form->field($model, 'website_url')->textInput(['maxlength' => true]) ?>

        <?= $form->field($model, 'linkman')->textInput(['maxlength' => true]) ?>

        <?= $form->field($model, 'mobile_phone')->textInput(['maxlength' => true]) ?>

        <?= $form->field($model, 'enabled')->checkbox(null, false) ?>

        <?= $form->field($model, 'remark')->textarea(['rows' => 6]) ?>
        <div class="form-group buttons">
            <?= Html::submitButton('保存', ['class' => 'btn btn-success']) ?>
        </div>
        <?php ActiveForm::end(); ?>
    </div>
</div>

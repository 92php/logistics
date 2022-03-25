<?php

use app\models\Option;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\modules\admin\modules\g\models\Country */
/* @var $form yii\widgets\ActiveForm */
?>
<div class="form-outside">
    <div class="country-form form">
        <?php $form = ActiveForm::begin(); ?>

        <?= $form->field($model, 'region_id')->dropDownList(Option::regions(), ['prompt' => '']) ?>

        <?= $form->field($model, 'abbreviation')->textInput(['maxlength' => true]) ?>

        <?= $form->field($model, 'chinese_name')->textInput(['maxlength' => true]) ?>

        <?= $form->field($model, 'english_name')->textInput(['maxlength' => true]) ?>
        <div class="form-group buttons">
            <?= Html::submitButton('保存', ['class' => 'btn btn-success']) ?>
        </div>
        <?php ActiveForm::end(); ?>
    </div>
</div>

<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\modules\admin\modules\g\models\Package */
/* @var $form yii\widgets\ActiveForm */
?>
<div class="form-outside">
    <div class="package-form form">
        <?php $form = ActiveForm::begin(); ?>

        <?= $form->field($model, 'key')->textInput(['maxlength' => true]) ?>

        <?= $form->field($model, 'number')->textInput(['maxlength' => true]) ?>

        <?= $form->field($model, 'waybill_number')->textInput(['maxlength' => true]) ?>

        <?= $form->field($model, 'weight')->textInput() ?>

        <?= $form->field($model, 'freight_cost')->textInput(['maxlength' => true]) ?>

        <?= $form->field($model, 'delivery_datetime')->textInput() ?>

        <?= $form->field($model, 'logistics_line_id')->textInput() ?>

        <?= $form->field($model, 'logistics_query_raw_results')->textarea(['rows' => 6]) ?>

        <?= $form->field($model, 'logistics_last_check_datetime')->textInput() ?>

        <?= $form->field($model, 'status')->textInput() ?>

        <?= $form->field($model, 'remark')->textarea(['rows' => 6]) ?>
        <div class="form-group buttons">
            <?= Html::submitButton('保存', ['class' => 'btn btn-success']) ?>
        </div>
        <?php ActiveForm::end(); ?>
    </div>
</div>

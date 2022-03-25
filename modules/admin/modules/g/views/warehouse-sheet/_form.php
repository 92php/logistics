<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\modules\admin\modules\g\models\WarehouseSheet */
/* @var $form yii\widgets\ActiveForm */
?>
<div class="form-outside">
    <div class="warehouse-sheet-form form">
        <?php $form = ActiveForm::begin(); ?>

        <?= $form->field($model, 'warehouse_id')->textInput() ?>

        <?= $form->field($model, 'number')->textInput(['maxlength' => true]) ?>

        <?= $form->field($model, 'type')->textInput() ?>

        <?= $form->field($model, 'method')->textInput() ?>

        <?= $form->field($model, 'change_datetime')->textInput() ?>

        <?= $form->field($model, 'operation_member_id')->textInput() ?>

        <?= $form->field($model, 'operation_datetime')->textInput() ?>

        <?= $form->field($model, 'remark')->textarea(['rows' => 6]) ?>

        <?= $form->field($model, 'created_at')->textInput() ?>

        <?= $form->field($model, 'created_by')->textInput() ?>
        <div class="form-group buttons">
            <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
        </div>
        <?php ActiveForm::end(); ?>
    </div>
</div>

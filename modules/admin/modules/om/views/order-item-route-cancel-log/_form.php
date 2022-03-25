<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\modules\admin\modules\om\models\OrderItemRouteCancelLog */
/* @var $form yii\widgets\ActiveForm */
?>
<div class="form-outside">
    <div class="cancel-log-form form">
        <?php $form = ActiveForm::begin(); ?>

        <?= $form->field($model, 'order_item_route_id')->textInput() ?>

        <?= $form->field($model, 'canceled_reason')->textInput(['maxlength' => true]) ?>

        <?= $form->field($model, 'canceled_quantity')->textInput() ?>

        <?= $form->field($model, 'canceled_at')->textInput() ?>

        <?= $form->field($model, 'canceled_by')->textInput() ?>

        <?= $form->field($model, 'confirmed_status')->textInput() ?>

        <?= $form->field($model, 'confirmed_message')->textarea(['rows' => 6]) ?>

        <?= $form->field($model, 'confirmed_at')->textInput() ?>

        <?= $form->field($model, 'confirmed_by')->textInput() ?>
        <div class="form-group buttons">
            <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
        </div>
        <?php ActiveForm::end(); ?>
    </div>
</div>

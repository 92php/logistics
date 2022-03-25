<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\modules\admin\modules\om\models\OrderItemRoute */
/* @var $form yii\widgets\ActiveForm */
?>
<div class="form-outside">
    <div class="order-route-form form">
        <?php $form = ActiveForm::begin(); ?>

        <?= $form->field($model, 'order_item_id')->textInput() ?>

        <?= $form->field($model, 'place_order_at')->textInput() ?>

        <?= $form->field($model, 'place_order_by')->textInput() ?>

        <?= $form->field($model, 'vendor')->textInput() ?>

        <?= $form->field($model, 'receipt_at')->textInput() ?>

        <?= $form->field($model, 'receipt_status')->textInput() ?>

        <?= $form->field($model, 'production_at')->textInput() ?>

        <?= $form->field($model, 'vendor_deliver_at')->textInput() ?>

        <?= $form->field($model, 'receiving_at')->textInput() ?>

        <?= $form->field($model, 'inspection_at')->textInput() ?>

        <?= $form->field($model, 'warehousing_at')->textInput() ?>

        <?= $form->field($model, 'inspection_number')->textInput() ?>

        <?= $form->field($model, 'is_reissue')->textInput() ?>

        <?= $form->field($model, 'reason')->textarea(['rows' => 6]) ?>

        <?= $form->field($model, 'feedback')->textarea(['rows' => 6]) ?>

        <?= $form->field($model, 'information_feedback')->textarea(['rows' => 6]) ?>

        <?= $form->field($model, 'is_accord_with')->textInput() ?>

        <?= $form->field($model, 'is_information_match')->textInput() ?>
        <div class="form-group buttons">
            <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
        </div>
        <?php ActiveForm::end(); ?>
    </div>
</div>

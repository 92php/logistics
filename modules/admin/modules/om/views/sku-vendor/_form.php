<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\modules\admin\modules\om\models\SkuVendor */
/* @var $form yii\widgets\ActiveForm */
?>
<div class="form-outside">
    <div class="sku-vendor-form form">
        <?php $form = ActiveForm::begin(); ?>

        <?= $form->field($model, 'ordering')->textInput() ?>

        <?= $form->field($model, 'sku')->textInput(['maxlength' => true]) ?>

        <?= $form->field($model, 'vendor_id')->textInput() ?>

        <?= $form->field($model, 'cost_price')->textInput(['maxlength' => true]) ?>

        <?= $form->field($model, 'production_min_days')->textInput(['type' => 'number']) ?>

        <?= $form->field($model, 'production_max_days')->textInput(['type' => 'number']) ?>

        <?= $form->field($model, 'enabled')->checkbox(null, false) ?>

        <?= $form->field($model, 'remark')->textarea(['rows' => 6]) ?>
        <div class="form-group buttons">
            <?= Html::submitButton('保存', ['class' => 'btn btn-success']) ?>
        </div>
        <?php ActiveForm::end(); ?>
    </div>
</div>

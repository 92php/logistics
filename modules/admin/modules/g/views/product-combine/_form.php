<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\modules\admin\modules\g\models\ProductCombine */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="form-outside">
    <div class="product-combine-form form">

        <?php $form = ActiveForm::begin(); ?>

        <?= $form->field($model, 'product_id')->textInput() ?>

    <?= $form->field($model, 'child_product_id')->textInput() ?>

        <div class="form-group buttons">
            <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
        </div>

        <?php ActiveForm::end(); ?>
    </div>
</div>

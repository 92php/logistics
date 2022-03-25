<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\modules\admin\modules\g\models\CustomerAddressSearch */
/* @var $form yii\widgets\ActiveForm */
?>
<div class="form-outside form-search form-layout-column">
    <div class="customer-address-search form">
        <?php $form = ActiveForm::begin([
            'action' => ['index'],
            'method' => 'get',
            'options' => [
                'data-pjax' => 1
            ],
        ]); ?>

        <?= $form->field($model, 'id') ?>

        <?= $form->field($model, 'customer_id') ?>

        <?= $form->field($model, 'key') ?>

        <?= $form->field($model, 'first_name') ?>

        <?= $form->field($model, 'last_name') ?>

        <?php // echo $form->field($model, 'company') ?>

        <?php // echo $form->field($model, 'address1') ?>

        <?php // echo $form->field($model, 'address2') ?>

        <?php // echo $form->field($model, 'country_id') ?>

        <?php // echo $form->field($model, 'province') ?>

        <?php // echo $form->field($model, 'city') ?>

        <?php // echo $form->field($model, 'zip') ?>

        <?php // echo $form->field($model, 'phone') ?>
        <div class="form-group buttons">
            <?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?>
            <?= Html::resetButton('Reset', ['class' => 'btn btn-outline-secondary']) ?>
        </div>
        <?php ActiveForm::end(); ?>
    </div>
</div>

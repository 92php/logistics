<?php

use app\models\Option;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\modules\admin\modules\g\models\OrderItemSearch */
/* @var $form yii\widgets\ActiveForm */
?>
<div class="form-outside form-search form-layout-column">
    <div class="order-item-search form">
        <?php $form = ActiveForm::begin([
            'action' => ['index'],
            'method' => 'get',
            'options' => [
                'data-pjax' => 1
            ],
        ]); ?>
        <div class="entry">
            <?= $form->field($model, 'order_number') ?>

            <?= $form->field($model, 'sku') ?>
        </div>
        <div class="entry">
            <?= $form->field($model, 'product_name') ?>

            <?= $form->field($model, 'ignored')->dropDownList(Option::boolean(), ['prompt' => '']) ?>
        </div>
        <?php // echo $form->field($model, 'extend') ?>

        <?php // echo $form->field($model, 'quantity') ?>

        <?php // echo $form->field($model, 'vendor_id') ?>

        <?php // echo $form->field($model, 'sale_price') ?>

        <?php // echo $form->field($model, 'cost_price') ?>

        <?php // echo $form->field($model, 'remark') ?>
        <div class="form-group buttons">
            <?= Html::submitButton('搜索', ['class' => 'btn btn-primary']) ?>
            <?= Html::resetButton('重置', ['class' => 'btn btn-outline-secondary']) ?>
        </div>
        <?php ActiveForm::end(); ?>
    </div>
</div>

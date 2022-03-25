<?php

use app\models\Option;
use app\modules\admin\modules\g\models\Country;
use app\modules\admin\modules\g\models\Order;
use app\modules\admin\modules\g\models\Shop;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\modules\admin\modules\g\models\Order */
/* @var $form yii\widgets\ActiveForm */
?>
<div class="form-outside">
    <div class="order-form form">
        <?php $form = ActiveForm::begin(); ?>
        <div class="entry">
            <?= $form->field($model, 'number')->textInput(['maxlength' => true]) ?>

            <?= $form->field($model, 'country_id')->dropDownList(Country::map(), ['prompt' => '']) ?>
        </div>
        <div class="entry">
            <?= $form->field($model, 'consignee_name')->textInput(['maxlength' => true]) ?>

            <?= $form->field($model, 'consignee_mobile_phone')->textInput(['maxlength' => true]) ?>
        </div>
        <?= $form->field($model, 'consignee_tel')->textInput(['maxlength' => true]) ?>
        <div class="entry">
            <?= $form->field($model, 'consignee_state')->textInput(['maxlength' => true]) ?>

            <?= $form->field($model, 'consignee_city')->textInput(['maxlength' => true]) ?>
        </div>
        <div class="entry">
            <?= $form->field($model, 'consignee_address1')->textInput(['maxlength' => true]) ?>

            <?= $form->field($model, 'consignee_address2')->textInput(['maxlength' => true]) ?>
        </div>
        <div class="entry">
            <?= $form->field($model, 'consignee_postcode')->textInput(['maxlength' => true]) ?>

            <?= $form->field($model, 'total_amount')->textInput(['maxlength' => true]) ?>
        </div>
        <?= $form->field($model, 'status')->dropDownList(Order::statusOptions(), ['prompt' => '']) ?>
        <div class="entry">
            <?= $form->field($model, 'platform_id')->dropDownList(Option::platforms(), ['prompt' => '']) ?>

            <?= $form->field($model, 'shop_id')->dropDownList(Shop::map(), ['prompt' => '']) ?>
        </div>
        <div class="entry">
            <?= $form->field($model, 'place_order_at')->textInput() ?>

            <?= $form->field($model, 'payment_at')->textInput() ?>
        </div>
        <div class="entry">
            <?= $form->field($model, 'cancelled_at')->textInput() ?>

            <?= $form->field($model, 'cancel_reason')->textInput() ?>
        </div>
        <div class="entry">
            <?= $form->field($model, 'closed_at')->textInput() ?>
        </div>
        <?= $form->field($model, 'remark')->textarea(['rows' => 6]) ?>
        <div class="form-group buttons">
            <?= Html::submitButton('保存', ['class' => 'btn btn-success']) ?>
        </div>
        <?php ActiveForm::end(); ?>
    </div>
</div>

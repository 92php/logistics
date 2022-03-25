<?php

use app\models\Category;
use app\models\Member;
use app\modules\admin\modules\g\models\Product;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\modules\admin\modules\g\models\Product */
/* @var $form yii\widgets\ActiveForm */
?>
<div class="form-outside">
    <div class="product-form form">
        <?php $form = ActiveForm::begin(); ?>

        <?= $form->field($model, 'key')->textInput(['maxlength' => true]) ?>

        <?= $form->field($model, 'category_id')->dropDownList(Category::tree('g.product.category_id')) ?>

        <?= $form->field($model, 'sku')->textInput(['maxlength' => true]) ?>

        <?= $form->field($model, 'chinese_name')->textInput(['maxlength' => true]) ?>

        <?= $form->field($model, 'english_name')->textInput(['maxlength' => true]) ?>

        <?= $form->field($model, 'weight')->textInput(['maxlength' => true, 'type' => 'number']) ?>

        <?= $form->field($model, 'size_length')->textInput(['maxlength' => true, 'type' => 'number']) ?>

        <?= $form->field($model, 'size_width')->textInput(['maxlength' => true, 'type' => 'number']) ?>

        <?= $form->field($model, 'size_height')->textInput(['maxlength' => true, 'type' => 'number']) ?>

        <?= $form->field($model, 'allow_offset_weight')->textInput(['maxlength' => true, 'type' => 'number']) ?>

        <?= $form->field($model, 'stock_quantity')->textInput(['maxlength' => true, 'type' => 'number']) ?>

        <?= $form->field($model, 'status')->dropDownList(Product::statusOptions()) ?>

        <?= $form->field($model, 'price')->textInput(['type' => 'number']) ?>

        <?= $form->field($model, 'image')->fileInput() ?>

        <?= $form->field($model, 'cost_price')->textInput(['type' => 'number']) ?>

        <?= $form->field($model, 'development_member_id')->dropDownList(Member::map()) ?>

        <?= $form->field($model, 'purchase_member_id')->dropDownList(Member::map()) ?>

        <?= $form->field($model, 'purchase_reference_price')->textInput(['type' => 'number']) ?>

        <?= $form->field($model, 'qc_description')->textarea() ?>
        <div class="form-group buttons">
            <?= Html::submitButton('保存', ['class' => 'btn btn-success']) ?>
        </div>
        <?php ActiveForm::end(); ?>
    </div>
</div>

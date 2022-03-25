<?php

use app\models\Member;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\modules\admin\modules\g\models\Vendor */
/* @var $form yii\widgets\ActiveForm */
?>
<div class="form-outside">
    <div class="vendor-form form">
        <?php $form = ActiveForm::begin(); ?>
        <div class="entry">
            <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>

            <?= $form->field($model, 'address')->textInput(['maxlength' => true]) ?>
        </div>
        <div class="entry">
            <?= $form->field($model, 'linkman')->textInput(['maxlength' => true]) ?>
        </div>
        <div class="entry">
            <?= $form->field($model, 'mobile_phone')->textInput(['maxlength' => true]) ?>
            <?= $form->field($model, 'tel')->textInput(['maxlength' => true]) ?>
        </div>
        <div class="entry">
            <?= $form->field($model, 'receipt_duration')->textInput() ?>

            <?= $form->field($model, 'production')->textInput(['type' => 'number']) ?>
        </div>
        <div class="entry">
            <?= $form->field($model, 'credibility')->textInput() ?>

            <?= $form->field($model, 'enabled')->checkbox(null, false) ?>
        </div>
        <?= $form->field($model, 'member_ids')->checkboxList(Member::map()) ?>

        <?= $form->field($model, 'remark')->textarea(['rows' => 6]) ?>
        <div class="form-group buttons">
            <?= Html::submitButton('保存', ['class' => 'btn btn-success']) ?>
        </div>
        <?php ActiveForm::end(); ?>
    </div>
</div>

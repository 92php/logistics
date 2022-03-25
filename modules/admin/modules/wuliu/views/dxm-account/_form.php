<?php

use app\models\Option;
use app\modules\admin\modules\wuliu\models\Company;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\modules\admin\modules\wuliu\models\DxmAccount */
/* @var $form yii\widgets\ActiveForm */
?>
<div class="form-outside">
    <div class="dxm-account-form form">
        <?php $form = ActiveForm::begin(); ?>
        <?= $form->field($model, 'username')->textInput(['maxlength' => true]) ?>

        <?= $form->field($model, 'company_id')->textInput()->dropDownList(Company::map(), ['prompt' => '']) ?>

        <?= $form->field($model, 'platform_id')->textInput()->dropDownList(Option::platforms(), ['prompt' => '']) ?>

        <?= $form->field($model, 'is_valid')->checkbox(null, false) ?>

        <?= $form->field($model, 'cookies')->textarea() ?>

        <?= $form->field($model, 'remark')->textInput(['maxlength' => true]) ?>
        <div class="form-group buttons">
            <?= Html::submitButton('保存', ['class' => 'btn btn-success']) ?>
        </div>
        <?php ActiveForm::end(); ?>
    </div>
</div>

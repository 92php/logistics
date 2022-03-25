<?php

use app\modules\admin\modules\wuliu\models\Company;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\modules\admin\modules\wuliu\models\CompanyLine */
/* @var $form yii\widgets\ActiveForm */
?>
<div class="form-outside">
    <div class="company-line-form form">
        <?php $form = ActiveForm::begin(); ?>

        <?= $form->field($model, 'company_id')->dropDownList(Company::map(), ['prompt' => '']) ?>

        <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>

        <?= $form->field($model, 'estimate_days')->textInput(['type' => 'number']) ?>

        <?= $form->field($model, 'enabled')->checkbox(null, false) ?>

        <?= $form->field($model, 'remark')->textarea(['rows' => 6]) ?>
        <div class="form-group buttons">
            <?= Html::submitButton('保存', ['class' => 'btn btn-success']) ?>
        </div>
        <?php ActiveForm::end(); ?>
    </div>
</div>

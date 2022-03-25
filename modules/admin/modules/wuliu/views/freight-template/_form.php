<?php

use app\modules\admin\modules\wuliu\models\Company;
use app\modules\admin\modules\wuliu\models\FreightTemplate;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\modules\admin\modules\wuliu\models\FreightTemplate */
/* @var $form yii\widgets\ActiveForm */
?>
<div class="form-outside">
    <div class="freight-template-form form">
        <?php $form = ActiveForm::begin(); ?>

        <?= $form->field($model, 'company_id')->dropDownList(Company::map()); ?>

        <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>

        <?= $form->field($model, 'fee_mode')->dropDownList(FreightTemplate::feeModeOptions()) ?>

        <?= $form->field($model, 'enabled')->checkbox(null, false) ?>

        <?= $form->field($model, 'remark')->textarea(['rows' => 6]) ?>
        <div class="form-group buttons">
            <?= Html::submitButton('保存', ['class' => 'btn btn-success']) ?>
        </div>
        <?php ActiveForm::end(); ?>
    </div>
</div>

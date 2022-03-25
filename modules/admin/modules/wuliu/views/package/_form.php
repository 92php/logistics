<?php

use app\modules\admin\modules\wuliu\models\DxmAccount;
use app\modules\admin\modules\wuliu\models\Package;
use app\modules\api\modules\g\models\Country;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\modules\admin\modules\wuliu\models\Package */
/* @var $form yii\widgets\ActiveForm */
?>
<div class="form-outside">
    <div class="package-form form">
        <?php $form = ActiveForm::begin(); ?>

        <?= $form->field($model, 'package_id')->textInput(['maxlength' => true]) ?>

        <?= $form->field($model, 'package_number')->textInput(['maxlength' => true]) ?>

        <?= $form->field($model, 'order_number')->textInput(['maxlength' => true]) ?>

        <?= $form->field($model, 'line_id')->textInput() ?>

        <?= $form->field($model, 'waybill_number')->textInput(['maxlength' => true]) ?>

        <?= $form->field($model, 'country_id')->dropDownList(Country::map(), ['prompt' => '']) ?>

        <?= $form->field($model, 'weight')->textInput() ?>

        <?= $form->field($model, 'freight_cost')->textInput(['maxlength' => true]) ?>

        <?= $form->field($model, 'dxm_account_id')->dropDownList(DxmAccount::map(), ['prompt' => '']) ?>

        <?= $form->field($model, 'shop_name')->textInput(['maxlength' => true]) ?>

        <?= $form->field($model, 'delivery_datetime')->textInput() ?>

        <?= $form->field($model, 'estimate_days')->textInput() ?>

        <?= $form->field($model, 'final_days')->textInput() ?>

        <?= $form->field($model, 'logistics_query_raw_results')->textarea(['rows' => 6]) ?>

        <?= $form->field($model, 'sync_status')->dropDownList(Package::syncStatusOptions()) ?>

        <?= $form->field($model, 'status')->textInput() ?>

        <?= $form->field($model, 'remark')->textarea(['rows' => 6]) ?>

        <?= $form->field($model, 'created_at')->textInput() ?>

        <?= $form->field($model, 'created_by')->textInput() ?>

        <?= $form->field($model, 'updated_at')->textInput() ?>

        <?= $form->field($model, 'updated_by')->textInput() ?>
        <div class="form-group buttons">
            <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
        </div>
        <?php ActiveForm::end(); ?>
    </div>
</div>

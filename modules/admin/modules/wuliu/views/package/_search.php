<?php

use app\modules\admin\modules\g\models\Country;
use app\modules\admin\modules\wuliu\models\DxmAccount;
use app\modules\admin\modules\wuliu\models\Package;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\modules\admin\modules\wuliu\models\PackageSearch */
/* @var $form yii\widgets\ActiveForm */
?>
<div class="form-outside form-search form-layout-column">
    <div class="package-search form">
        <?php $form = ActiveForm::begin([
            'action' => ['index'],
            'method' => 'get',
            'options' => [
                'data-pjax' => 1
            ],
        ]); ?>
        <div class="entry">
            <?= $form->field($model, 'id') ?>
        </div>
        <div class="entry">
            <?= $form->field($model, 'package_number') ?>

            <?= $form->field($model, 'order_number') ?>
        </div>
        <div class="entry">
            <?= $form->field($model, 'waybill_number') ?>

            <?= $form->field($model, 'country_id')->dropDownList(Country::map(), ['prompt' => '']) ?>
        </div>
        <div class="entry">
            <?= $form->field($model, 'dxm_account_id')->dropDownList(DxmAccount::map(), ['prompt' => '']) ?>

            <?= $form->field($model, 'status')->dropDownList(Package::statusOptions(), ['prompt' => '']) ?>
        </div>
        <div class="form-group buttons">
            <?= Html::submitButton('搜索', ['class' => 'btn btn-primary']) ?>
            <?= Html::resetButton('重置', ['class' => 'btn btn-outline-secondary']) ?>
        </div>
        <?php ActiveForm::end(); ?>
    </div>
</div>

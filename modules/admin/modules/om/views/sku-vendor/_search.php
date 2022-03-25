<?php

use app\models\Option;
use app\modules\admin\modules\g\models\Vendor;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\modules\admin\modules\om\models\SkuVendorSearch */
/* @var $form yii\widgets\ActiveForm */
?>
<div class="form-outside form-search form-layout-column">
    <div class="sku-vendor-search form">
        <?php $form = ActiveForm::begin([
            'action' => ['index'],
            'method' => 'get',
            'options' => [
                'data-pjax' => 1
            ],
        ]); ?>
        <div class="entry">
            <?= $form->field($model, 'sku') ?>
            <?= $form->field($model, 'vendor_id')->dropDownList(Vendor::map(), ['prompt' => '请选择']) ?>
        </div>
        <div class="entry">
            <?= $form->field($model, 'enabled')->dropDownList(Option::boolean(), ['prompt' => '']) ?>
        </div>
        <div class="form-group buttons">
            <?= Html::submitButton('搜索', ['class' => 'btn btn-primary']) ?>
            <?= Html::resetButton('重置', ['class' => 'btn btn-outline-secondary']) ?>
        </div>
        <?php ActiveForm::end(); ?>
    </div>
</div>

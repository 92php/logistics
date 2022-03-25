<?php

use app\models\Option;
use app\modules\admin\modules\g\models\Shop;
use app\modules\admin\modules\g\models\ThirdPartyAuthentication;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\modules\admin\modules\g\models\Shop */
/* @var $form yii\widgets\ActiveForm */
?>
<div>
    <ul class="tabs-common">
        <li class="active"><a href="javascript:;" data-toggle="tab-panel-basic">基本数据</a></li>
        <?php if ($dynamicModel->getMetaOptions()): ?>
            <li><a href="javascript:;" data-toggle="tab-panel-metas">扩展属性</a></li>
        <?php endif; ?>
    </ul>
    <div class="panels form form-layout-column">
        <?php $form = ActiveForm::begin(); ?>
        <div class="tab-panel" id="tab-panel-basic">
            <div class="entry">
                <?= $form->field($model, 'organization_id')->dropDownList(Option::organizations(), ['prompt' => '']) ?>

                <?= $form->field($model, 'platform_id')->dropDownList(Option::platforms(), ['prompt' => '']) ?>
            </div>
            <div class="entry">
                <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>

                <?= $form->field($model, 'product_type')->dropDownList(Shop::productTypeOptions()) ?>
            </div>
            <?= $form->field($model, 'url')->textInput(['maxlength' => true, 'style' => 'width: 74%']) ?>
            <div class="entry">
                <?= $form->field($model, 'third_party_authentication_id')->dropDownList(ThirdPartyAuthentication::map(), ['prompt' => '']) ?>

                <?= $form->field($model, 'third_party_sign') ?>
            </div>
            <div class="entry">
                <?= $form->field($model, 'remark')->textarea(['rows' => 6]) ?>

                <?= $form->field($model, 'enabled')->checkbox(null, false) ?>
            </div>
        </div>
        <div class="tab-panel" id="tab-panel-metas" style="display: none">
            <?php foreach ($dynamicModel->getMetaOptions() as $metaItem): ?>
                <?= $form->field($dynamicModel, $metaItem['key'])->{$metaItem['input_type']}(['value' => $metaItem['value']])->label($metaItem['label']) ?>
            <?php endforeach; ?>
        </div>
        <div class="form-group buttons">
            <?= Html::submitButton('保存', ['class' => 'btn btn-success']) ?>
        </div>
        <?php ActiveForm::end(); ?>
    </div>
</div>
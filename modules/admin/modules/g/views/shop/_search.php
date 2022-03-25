<?php

use app\models\Option;
use app\modules\admin\modules\g\models\Shop;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\modules\admin\modules\g\models\ShopSearch */
/* @var $form yii\widgets\ActiveForm */
?>
<div class="form-outside form-search form-layout-column">
    <div class="shop-search form">
        <?php $form = ActiveForm::begin([
            'action' => ['index'],
            'method' => 'get',
            'options' => [
                'data-pjax' => 1
            ],
        ]); ?>
        <div class="entry">
            <?= $form->field($model, 'organization_id')->dropDownList(Option::organizations(), ['prompt' => '']) ?>

            <?= $form->field($model, 'platform_id')->dropDownList(Option::platforms(), ['prompt' => '']) ?>
        </div>
        <div class="entry">
            <?= $form->field($model, 'name') ?>

            <?= $form->field($model, 'enabled')->dropDownList(Option::boolean(), ['prompt' => '']) ?>
        </div>
        <div class="entry">
            <?= $form->field($model, 'product_type')->dropDownList(Shop::productTypeOptions(), ['prompt' => '']) ?>
        </div>
        <div class="entry">
            <?= $form->field($model, 'third_party_platform_id')->dropDownList(Option::thirdPartyPlatforms(), ['prompt' => '']) ?>
            <?= $form->field($model, 'third_party_sign') ?>
        </div>
        <div class="form-group buttons">
            <?= Html::submitButton('搜索', ['class' => 'btn btn-primary']) ?>
            <?= Html::resetButton('重置', ['class' => 'btn btn-outline-secondary']) ?>
        </div>
        <?php ActiveForm::end(); ?>
    </div>
</div>

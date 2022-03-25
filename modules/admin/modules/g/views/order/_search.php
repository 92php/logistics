<?php

use app\models\Option;
use app\modules\admin\modules\g\models\Country;
use app\modules\admin\modules\g\models\Order;
use app\modules\admin\modules\g\models\Shop;
use yadjet\datePicker\my97\DatePicker;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\modules\admin\modules\g\models\OrderSearch */
/* @var $form yii\widgets\ActiveForm */
?>
<div class="form-outside form-search form-layout-column">
    <div class="order-search form">
        <?php $form = ActiveForm::begin([
            'action' => ['index'],
            'method' => 'get',
            'options' => [
                'data-pjax' => 1
            ],
        ]); ?>
        <div class="entry">
            <?= $form->field($model, 'organization_id')->dropDownList(Option::organizations(), ['prompt' => '']) ?>
            <?= $form->field($model, 'type')->dropDownList(Order::typeOptions(), ['prompt' => '']) ?>
        </div>
        <div class="entry">
            <?= $form->field($model, 'number') ?>
            <?= $form->field($model, 'country_id')->dropDownList(Country::map(), ['prompt' => '']) ?>
        </div>
        <div class="entry">
            <?= $form->field($model, 'shop_id')->dropDownList(Shop::map(), ['prompt' => '']) ?>
            <?= $form->field($model, 'product_type')->dropDownList(Shop::productTypeOptions(), ['prompt' => '']) ?>
        </div>
        <div class="entry">
            <?= $form->field($model, 'third_party_platform_id')->dropDownList(Option::thirdPartyPlatforms(), ['prompt' => '']) ?>
            <?= $form->field($model, 'status')->dropDownList(Order::statusOptions(), ['prompt' => '']) ?>
        </div>
        <div class="entry">
            <?= DatePicker::widget([
                'form' => $form,
                'model' => $model,
                'attribute' => 'begin_date',
                'pickerType' => 'date',
            ]) ?>

            <?= DatePicker::widget([
                'form' => $form,
                'model' => $model,
                'attribute' => 'end_date',
                'pickerType' => 'date',
            ]) ?>
        </div>
        <div class="form-group buttons">
            <?= Html::submitButton('搜索', ['class' => 'btn btn-primary']) ?>
            <?= Html::resetButton('重置', ['class' => 'btn btn-outline-secondary']) ?>
        </div>
        <?php ActiveForm::end(); ?>
    </div>
</div>
<?php
$baseUrl = Yii::$app->getRequest()->getBaseUrl() . '/admin';
$this->registerJsFile($baseUrl . '/chosen/chosen.jquery.min.js', ['depends' => 'yii\web\JqueryAsset']);
$this->registerCssFile($baseUrl . '/chosen/chosen.min.css');
$js = <<<EOT
$('#ordersearch-shop_id').chosen({
    no_results_text: '无匹配店铺：',
    placeholder_text_multiple: '点击此处，在空白框内输入或选择店铺名称',
    width: '80%',
    search_contains: true,
    allow_single_deselect: true
});
EOT;
$this->registerJs($js);
?>

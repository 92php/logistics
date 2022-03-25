<?php

use app\components\JsBlock;
use app\models\Constant;
use app\models\Option;
use app\modules\admin\modules\wuliu\models\Company;
use app\modules\admin\modules\wuliu\models\CompanyLine;
use yadjet\datePicker\my97\DatePicker;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

$this->title = '报表处理';

?>
<h1><?= Html::encode($this->title) ?></h1>
<div class="form-outside">
    <div class="form">
        <?php $form = ActiveForm::begin(['action' => ['report'], 'method' => 'post']); ?>
        <div class="entry">
            <?= DatePicker::widget([
                'form' => $form,
                'model' => $model,
                'attribute' => 'beginDate',
                'pickerType' => 'date',
            ]) ?>
            <?= DatePicker::widget([
                'form' => $form,
                'model' => $model,
                'attribute' => 'endDate',
                'pickerType' => 'date',
            ]) ?>
        </div>
        <?= $form->field($model, 'platform_id')->dropDownList(Option::platforms(), ['prompt' => '请选择']) ?>
        <?= $form->field($model, 'logistics_provider_id')->dropDownList(Company::map(), ['prompt' => '请选择']) ?>
        <?= $form->field($model, 'channel_id')->dropDownList(CompanyLine::map(),  ['prompt' => '请选择']) ?>
        <div class="form-group buttons">
            <?= Html::submitButton('提交', ['class' => 'btn btn-success']) ?>
            <?= Html::resetButton('重置', ['class' => 'btn btn-primary', 'name' => 'submit-button']) ?>
        </div>
        <?php ActiveForm::end() ?>
    </div>
</div>
<?php
$baseUrl = Yii::$app->getRequest()->getBaseUrl() . '/admin';
$this->registerJsFile($baseUrl . '/chosen/chosen.jquery.min.js', ['depends' => 'yii\web\JqueryAsset']);
$this->registerCssFile($baseUrl . '/chosen/chosen.min.css');
?>
<?php JsBlock::begin() ?>
<script>
    $('#packagereportform-platform_id').chosen({
        no_results_text: '无匹配节点：',
        placeholder_text_multiple: '点击此处，在空白框内输入或选择节点名称',
        width: '80%',
        search_contains: true,
        allow_single_deselect: true
    });

    $('#packagereportform-logistics_provider_id').chosen({
        no_results_text: '无匹配节点：',
        placeholder_text_multiple: '点击此处，在空白框内输入或选择节点名称',
        width: '80%',
        search_contains: true,
        allow_single_deselect: true
    });

    $('#packagereportform-channel_id').chosen({
        no_results_text: '无匹配节点：',
        placeholder_text_multiple: '点击此处，在空白框内输入或选择节点名称',
        width: '80%',
        search_contains: true,
        allow_single_deselect: true
    });
</script>
<?php JsBlock::end() ?>


<?php

use app\modules\admin\modules\wuliu\models\CompanyLine;
use app\modules\admin\modules\wuliu\models\FreightTemplate;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\modules\admin\modules\wuliu\models\FreightTemplateFee */
/* @var $form yii\widgets\ActiveForm */
?>
<div class="form-outside">
    <div class="freight-template-fee-form form">
        <?php $form = ActiveForm::begin(); ?>

        <?= $form->field($model, 'template_id')->dropDownList(FreightTemplate::map(), ['prompt' => '请选择']) ?>

        <?= $form->field($model, 'line_id')->dropDownList(CompanyLine::map(), ['prompt' => '请选择']) ?>

        <?= $form->field($model, 'min_weight')->textInput(['type' => 'number']) ?>

        <?= $form->field($model, 'max_weight')->textInput(['type' => 'number']) ?>

        <?= $form->field($model, 'first_weight')->textInput(['type' => 'number']) ?>

        <?= $form->field($model, 'first_fee')->textInput(['type' => 'number', 'step' => '0.01']) ?>

        <?= $form->field($model, 'continued_weight')->textInput(['type' => 'number']) ?>

        <?= $form->field($model, 'continued_fee')->textInput(['type' => 'number', 'step' => '0.01']) ?>

        <?= $form->field($model, 'base_fee')->textInput(['type' => 'number', 'step' => '0.01']) ?>

        <?= $form->field($model, 'enabled')->checkbox(null, false) ?>

        <?= $form->field($model, 'remark')->textarea(['rows' => 6]) ?>
        <div class="form-group buttons">
            <?= Html::submitButton('保存', ['class' => 'btn btn-success']) ?>
        </div>
        <?php ActiveForm::end(); ?>
    </div>
</div>

<?php
$baseUrl = Yii::$app->getRequest()->getBaseUrl() . '/admin';
$this->registerJsFile($baseUrl . '/chosen/chosen.jquery.min.js', ['depends' => 'yii\web\JqueryAsset']);
$this->registerCssFile($baseUrl . '/chosen/chosen.min.css');
$js = <<<EOT
$('#freighttemplatefee-line_id').chosen({
    no_results_text: '无匹配节点：',
    placeholder_text_multiple: '点击此处，在空白框内输入或选择节点名称',
    width: '25%',
    search_contains: true,
    allow_single_deselect: true
});
EOT;
$this->registerJs($js);

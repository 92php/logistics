<?php

use app\modules\admin\modules\wuliu\models\CompanyLine;
use app\modules\admin\modules\wuliu\models\FreightTemplate;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\modules\admin\modules\wuliu\models\FreightTemplateFeeSearch */
/* @var $form yii\widgets\ActiveForm */
?>
<div class="form-outside form-search form-layout-column">
    <div class="freight-template-fee-search form">
        <?php $form = ActiveForm::begin([
            'action' => ['index'],
            'method' => 'get',
            'options' => [
                'data-pjax' => 1
            ],
        ]); ?>
        <div class="entry">
            <?= $form->field($model, 'template_id')->dropDownList(FreightTemplate::map(), ['prompt' => '请选择']) ?>

            <?= $form->field($model, 'line_id')->dropDownList(CompanyLine::map(), ['prompt' => '请选择']) ?>
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
$('#freighttemplatefeesearch-line_id').chosen({
    no_results_text: '无匹配节点：',
    placeholder_text_multiple: '点击此处，在空白框内输入或选择节点名称',
    width: '80%',
    search_contains: true,
    allow_single_deselect: true
});
EOT;
$this->registerJs($js);
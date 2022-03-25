<?php

use app\models\Option;
use app\modules\admin\modules\wuliu\models\CompanyLine;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\modules\admin\modules\wuliu\models\CompanyLineRouteSearch */
/* @var $form yii\widgets\ActiveForm */
?>
<div class="form-outside form-search form-layout-column">
    <div class="company-line-route-search form">
        <?php $form = ActiveForm::begin([
            'action' => ['index'],
            'method' => 'get',
            'options' => [
                'data-pjax' => 1
            ],
        ]); ?>
        <div class="entry">
            <?= $form->field($model, 'line_id')->dropDownList(CompanyLine::map(), ['prompt' => '']) ?>

            <?= $form->field($model, 'enabled')->dropDownList(Option::boolean(), ['prompt' => '']) ?>
        </div>
        <div class="entry">
            <?= $form->field($model, 'event') ?>

            <?= $form->field($model, 'detection_keyword') ?>
        </div>
        <?php // echo $form->field($model, 'estimate_days') ?>

        <?php // echo $form->field($model, 'enabled') ?>

        <?php // echo $form->field($model, 'created_at') ?>

        <?php // echo $form->field($model, 'created_by') ?>

        <?php // echo $form->field($model, 'updated_at') ?>

        <?php // echo $form->field($model, 'updated_by') ?>
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
$('#companylineroutesearch-line_id').chosen({
    no_results_text: '无匹配节点：',
    placeholder_text_multiple: '点击此处，在空白框内输入或选择节点名称',
    width: '80%',
    search_contains: true,
    allow_single_deselect: true
});
EOT;
$this->registerJs($js);

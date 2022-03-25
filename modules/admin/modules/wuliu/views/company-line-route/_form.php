<?php

use app\models\Option;
use app\modules\admin\modules\wuliu\models\CompanyLine;
use app\modules\admin\modules\wuliu\models\Package;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\modules\admin\modules\wuliu\models\CompanyLineRoute */
/* @var $form yii\widgets\ActiveForm */
?>
<div class="form-outside">
    <div class="company-line-route-form form">
        <?php $form = ActiveForm::begin(); ?>

        <?= $form->field($model, 'line_id')->dropDownList(CompanyLine::map(), ['prompt' => '']) ?>

        <?= $form->field($model, 'step')->dropDownList(Option::numbers(1, 20, 1, '第', '个监控点')) ?>

        <?= $form->field($model, 'event')->textInput(['maxlength' => true]) ?>

        <?= $form->field($model, 'detection_keyword')->textInput(['maxlength' => true]) ?>

        <?php
        $options = array_reverse(Option::numbers(1, 60, 1, null, '天'), true);
        $options[0] = '自动估算';
        $options[-1] = '不进行预估';
        $options = array_reverse($options, true);
        echo $form->field($model, 'estimate_days')->dropDownList($options);
        ?>

        <?php
        $options = Package::statusOptions();
        if (isset($options[Package::STATUS_PENDING])) {
            unset($options[Package::STATUS_PENDING]);
        }
        echo $form->field($model, 'package_status')->dropDownList($options, ['prompt' => '']);
        ?>

        <?= $form->field($model, 'enabled')->checkbox(null, false) ?>
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
$('#companylineroute-line_id').chosen({
    no_results_text: '无匹配节点：',
    placeholder_text_multiple: '点击此处，在空白框内输入或选择节点名称',
    width: '25%',
    search_contains: true,
    allow_single_deselect: true
});
EOT;
$this->registerJs($js);
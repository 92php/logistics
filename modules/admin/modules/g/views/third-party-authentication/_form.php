<?php

use app\helpers\Config;
use app\models\Option;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\modules\admin\modules\g\models\ThirdPartyAuthentication */
/* @var $form yii\widgets\ActiveForm */
?>
<div class="form-outside">
    <div class="third-party-authentication-form form">
        <?php $form = ActiveForm::begin(); ?>
        <div class="entry">
            <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>

            <?= $form->field($model, 'platform_id')->dropDownList(Option::thirdPartyPlatforms(), ['prompt' => '']) ?>
        </div>
        <?php
        $configurationPatterns = Config::get("platform.{$model->platform_id}", []);
        if ($configurationPatterns): ?>
            <div class="entry">
                <?php
                $i = 0;
                foreach ($configurationPatterns as $k => $patterns) {
                    foreach ($patterns as $kk => $pattern) {
                        $i++;
                        $field = $form->field($model, "authentication_config[$k][$kk]");
                        $type = (isset($pattern['type']) && $pattern['type']) ? strtolower($pattern['type']) : 'text';
                        switch ($type) {
                            case 'textarea':
                                $field->textarea();
                                break;

                            default:
                                $field->textInput();
                                break;
                        }
                        echo $field->label($pattern['label']);
                        if ($i % 2 == 0) {
                            echo "</div><div class='entry'>";
                        }
                    }
                }
                ?>
            </div>
        <?php endif; ?>
        <div class="entry">
            <?= $form->field($model, 'remark')->textarea(['rows' => 6]) ?>
            <?= $form->field($model, 'enabled')->checkbox(null, false) ?>
        </div>
        <div class="form-group buttons">
            <?= Html::submitButton('保存', ['class' => 'btn btn-success']) ?>
        </div>
        <?php ActiveForm::end(); ?>
    </div>
</div>

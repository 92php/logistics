<?php

use app\models\Option;
use app\modules\admin\modules\wuliu\models\Company;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\modules\admin\modules\wuliu\models\CompanyLineSearch */
/* @var $form yii\widgets\ActiveForm */
?>
<div class="form-outside form-search form-layout-column">
    <div class="company-line-search form">
        <?php $form = ActiveForm::begin([
            'action' => ['index'],
            'method' => 'get',
            'options' => [
                'data-pjax' => 1
            ],
        ]); ?>
        <div class="entry">
            <?= $form->field($model, 'company_id')->dropDownList(Company::map(), ['prompt' => '']) ?>

            <?= $form->field($model, 'name') ?>
        </div>
        <div class="entry">
            <?= $form->field($model, 'enabled')->dropDownList(Option::boolean(), ['prompt' => '请选择']) ?>
        </div>
        <div class="form-group buttons">
            <?= Html::submitButton('搜索', ['class' => 'btn btn-primary']) ?>
            <?= Html::resetButton('重置', ['class' => 'btn btn-outline-secondary']) ?>
        </div>
        <?php ActiveForm::end(); ?>
    </div>
</div>

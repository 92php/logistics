<?php

use app\models\Option;
use app\modules\admin\modules\g\models\Customer;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\modules\admin\modules\g\models\CustomerSearch */
/* @var $form yii\widgets\ActiveForm */
?>
<div class="form-outside form-search form-layout-column">
    <div class="customer-search form">
        <?php $form = ActiveForm::begin([
            'action' => ['index'],
            'method' => 'get',
            'options' => [
                'data-pjax' => 1
            ],
        ]); ?>
        <div class="entry">
            <?= $form->field($model, 'platform_id')->dropDownList(Option::platforms(), ['prompt' => '']) ?>
            <?= $form->field($model, 'status')->dropDownList(Customer::statusOptions(), ['prompt' => '']) ?>
        </div>
        <div class="entry">
            <?= $form->field($model, 'email') ?>
            <?= $form->field($model, 'phone') ?>
        </div>
        <div class="form-group buttons">
            <?= Html::submitButton('搜索', ['class' => 'btn btn-primary']) ?>
            <?= Html::resetButton('重置', ['class' => 'btn btn-outline-secondary']) ?>
        </div>
        <?php ActiveForm::end(); ?>
    </div>
</div>

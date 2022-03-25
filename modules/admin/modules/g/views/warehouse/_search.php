<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\modules\admin\modules\g\models\WarehouseSearch */
/* @var $form yii\widgets\ActiveForm */
?>
<div class="form-outside form-search form-layout-column">
    <div class="warehouse-search form">
        <?php $form = ActiveForm::begin([
            'action' => ['index'],
            'method' => 'get',
            'options' => [
                'data-pjax' => 1
            ],
        ]); ?>
        <div class="entry">
            <?= $form->field($model, 'name') ?>

            <?= $form->field($model, 'address') ?>
        </div>
        <div class="entry">
            <?= $form->field($model, 'linkman') ?>

            <?= $form->field($model, 'tel') ?>
        </div>
        <?= $form->field($model, 'enabled')->dropDownList([0 => '未激活', 1 => '激活'], ['prompt' => '']) ?>
        <div class="form-group buttons">
            <?= Html::submitButton('搜索', ['class' => 'btn btn-primary']) ?>
            <?= Html::resetButton('重置', ['class' => 'btn btn-outline-secondary']) ?>
        </div>
        <?php ActiveForm::end(); ?>
    </div>
</div>

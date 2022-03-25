<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\modules\admin\modules\g\models\Rack */
/* @var $form yii\widgets\ActiveForm */
?>
<div class="form-outside">
    <div class="rack-form form">
        <?php $form = ActiveForm::begin(); ?>

        <?= $form->field($model, 'warehouse_id')->textInput() ?>

        <?= $form->field($model, 'block')->textInput() ?>

        <?= $form->field($model, 'number')->textInput(['maxlength' => true]) ?>

        <?= $form->field($model, 'priority')->textInput() ?>

        <?= $form->field($model, 'remark')->textarea(['rows' => 6]) ?>

        <?= $form->field($model, 'created_at')->textInput() ?>

        <?= $form->field($model, 'created_by')->textInput() ?>

        <?= $form->field($model, 'updated_at')->textInput() ?>

        <?= $form->field($model, 'updated_by')->textInput() ?>
        <div class="form-group buttons">
            <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
        </div>
        <?php ActiveForm::end(); ?>
    </div>
</div>

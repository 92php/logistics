<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\modules\admin\modules\g\models\WarehouseSheetSearch */
/* @var $form yii\widgets\ActiveForm */
?>
<div class="form-outside form-search form-layout-column">
    <div class="warehouse-sheet-search form">
        <?php $form = ActiveForm::begin([
            'action' => ['index'],
            'method' => 'get',
            'options' => [
                'data-pjax' => 1
            ],
        ]); ?>

        <?= $form->field($model, 'id') ?>

        <?= $form->field($model, 'warehouse_id') ?>

        <?= $form->field($model, 'number') ?>

        <?= $form->field($model, 'type') ?>

        <?= $form->field($model, 'method') ?>

        <?php // echo $form->field($model, 'change_datetime') ?>

        <?php // echo $form->field($model, 'operation_member_id') ?>

        <?php // echo $form->field($model, 'operation_datetime') ?>

        <?php // echo $form->field($model, 'remark') ?>

        <?php // echo $form->field($model, 'created_at') ?>

        <?php // echo $form->field($model, 'created_by') ?>
        <div class="form-group buttons">
            <?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?>
            <?= Html::resetButton('Reset', ['class' => 'btn btn-outline-secondary']) ?>
        </div>
        <?php ActiveForm::end(); ?>
    </div>
</div>

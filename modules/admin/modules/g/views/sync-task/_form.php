<?php

use app\models\Option;
use app\modules\admin\modules\g\models\Shop;
use app\modules\admin\modules\g\models\SyncTask;
use yadjet\datePicker\my97\DatePicker;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\modules\admin\modules\g\models\SyncTask */
/* @var $form yii\widgets\ActiveForm */
?>
<div class="form-outside">
    <div class="sync-task-form form">
        <?php
        $form = ActiveForm::begin();
        $options = [];
        if (!$model->getIsNewRecord()) {
            $options['disabled'] = 'disabled';
        }
        ?>

        <?= $form->field($model, 'shop_id')->dropDownList(Shop::map(), $options) ?>

        <?= DatePicker::widget([
            'form' => $form,
            'model' => $model,
            'attribute' => 'begin_date',
            'pickerType' => 'date',
        ]) ?>


        <?= DatePicker::widget([
            'form' => $form,
            'model' => $model,
            'attribute' => 'end_date',
            'pickerType' => 'date',
        ]) ?>

        <?= $form->field($model, 'priority')->dropDownList(Option::numbers(0, 20)) ?>

        <?= $form->field($model, 'status')->dropDownList(SyncTask::statusOptions()) ?>
        <div class="form-group buttons">
            <?= Html::submitButton('保存', ['class' => 'btn btn-success']) ?>
        </div>
        <?php ActiveForm::end(); ?>
    </div>
</div>

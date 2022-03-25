<?php

use app\models\Option;
use app\modules\admin\modules\g\models\Tag;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\modules\admin\modules\g\models\Tag */
/* @var $form yii\widgets\ActiveForm */
?>
<div class="form-outside">
    <div class="tag-form form">
        <?php $form = ActiveForm::begin(); ?>

        <?= $form->field($model, 'type')->dropDownList(Tag::typeOptions()) ?>

        <?= $form->field($model, 'parent_id')->textInput() ?>

        <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>

        <?= $form->field($model, 'ordering')->dropDownList(Option::ordering()) ?>

        <?= $form->field($model, 'enabled')->checkbox(null, false) ?>
        <div class="form-group buttons">
            <?= Html::submitButton('保存', ['class' => 'btn btn-success']) ?>
        </div>
        <?php ActiveForm::end(); ?>
    </div>
</div>

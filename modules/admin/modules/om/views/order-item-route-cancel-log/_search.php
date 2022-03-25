<?php

use app\modules\admin\modules\om\models\OrderItemRouteCancelLog;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\modules\admin\modules\om\models\OrderItemRouteCancelLogSearch */
/* @var $form yii\widgets\ActiveForm */
?>
<div class="form-outside form-search form-layout-column">
    <div class="cancel-log-search form">
        <?php $form = ActiveForm::begin([
            'action' => ['index'],
            'method' => 'get',
        ]); ?>
        <div class="entry">
            <?= $form->field($model, 'type')->dropDownList(OrderItemRouteCancelLog::CancelTypeOption(), ['prompt' => '请选择']) ?>
            <?= $form->field($model, 'sku') ?>
        </div>
        <div class="form-group buttons">
            <?= Html::submitButton('搜索', ['class' => 'btn btn-primary']) ?>
            <?= Html::resetButton('保存', ['class' => 'btn btn-outline-secondary']) ?>
        </div>
        <?php ActiveForm::end(); ?>
    </div>
</div>

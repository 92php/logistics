<?php

use app\models\Option;
use app\modules\admin\modules\g\models\Shop;
use app\modules\admin\modules\g\models\SyncTask;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\modules\admin\modules\g\models\SyncTaskSearch */
/* @var $form yii\widgets\ActiveForm */
?>
<div class="form-outside form-search form-layout-column">
    <div class="sync-task-search form">
        <?php $form = ActiveForm::begin([
            'action' => ['index'],
            'method' => 'get',
            'options' => [
                'data-pjax' => 1
            ],
        ]); ?>
        <div class="entry">
            <?= $form->field($model, 'organization_id')->dropDownList(Option::organizations(), ['prompt' => '']) ?>
            <?= $form->field($model, 'shop_id')->dropDownList(Shop::map(), ['prompt' => '']) ?>
        </div>
        <div class="entry">
            <?= $form->field($model, 'status')->dropDownList(SyncTask::statusOptions(), ['prompt' => '']) ?>
        </div>
        <div class="form-group buttons">
            <?= Html::submitButton('搜索', ['class' => 'btn btn-primary']) ?>
            <?= Html::resetButton('重置', ['class' => 'btn btn-outline-secondary']) ?>
        </div>
        <?php ActiveForm::end(); ?>
    </div>
</div>

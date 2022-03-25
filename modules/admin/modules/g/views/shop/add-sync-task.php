<?php

use app\models\Option;
use yadjet\datePicker\my97\DatePicker;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\modules\admin\modules\g\models\Shop */

$this->title = '添加店铺同步任务';
$this->params['breadcrumbs'][] = ['label' => '店铺管理', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

$this->params['menus'] = [
    ['label' => '返回店铺管理', 'url' => ['shop/index']],
];

?>
<div class="shop-create">
    <div class="form-outside">
        <div class="sync-task-form form">
            <?php $form = ActiveForm::begin(); ?>

            <?= $form->field($model, 'shop_ids')->checkboxList($shops) ?>

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
            <div class="form-group buttons">
                <?= Html::submitButton('保存', ['class' => 'btn btn-success']) ?>
            </div>
            <?php ActiveForm::end(); ?>
        </div>
    </div>
</div>

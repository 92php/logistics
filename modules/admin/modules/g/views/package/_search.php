<?php

use app\modules\admin\modules\g\models\Country;
use app\modules\admin\modules\g\models\Shop;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\modules\admin\modules\g\models\PackageSearch */
/* @var $form yii\widgets\ActiveForm */
?>
<div class="form-outside form-search form-layout-column">
    <div class="package-search form">
        <?php $form = ActiveForm::begin([
            'action' => ['index'],
            'method' => 'get',
            'options' => [
                'data-pjax' => 1
            ],
        ]); ?>
        <div class="entry">
            <?= $form->field($model, 'id') ?>
        </div>
        <div class="entry">
            <?= $form->field($model, 'number') ?>
            <?= $form->field($model, 'waybill_number') ?>
        </div>
        <div class="entry">
            <?= $form->field($model, 'country_id')->dropDownList(Country::map(), ['prompt' => '']) ?>
            <?= $form->field($model, 'shop_id')->dropDownList(Shop::map(), ['prompt' => '']) ?>
        </div>
        <?php // echo $form->field($model, 'weight') ?>

        <?php // echo $form->field($model, 'freight_cost') ?>

        <?php // echo $form->field($model, 'delivery_datetime') ?>

        <?php // echo $form->field($model, 'logistics_line_id') ?>

        <?php // echo $form->field($model, 'logistics_query_raw_results') ?>

        <?php // echo $form->field($model, 'logistics_last_check_datetime') ?>

        <?php // echo $form->field($model, 'status') ?>

        <?php // echo $form->field($model, 'remark') ?>

        <?php // echo $form->field($model, 'created_at') ?>

        <?php // echo $form->field($model, 'created_by') ?>

        <?php // echo $form->field($model, 'updated_at') ?>

        <?php // echo $form->field($model, 'updated_by') ?>
        <div class="form-group buttons">
            <?= Html::submitButton('搜索', ['class' => 'btn btn-primary']) ?>
            <?= Html::resetButton('重置', ['class' => 'btn btn-outline-secondary']) ?>
        </div>
        <?php ActiveForm::end(); ?>
    </div>
</div>

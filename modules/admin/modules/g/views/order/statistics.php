<?php

use app\models\Option;
use yadjet\datePicker\my97\DatePicker;
use yii\grid\GridView;
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\widgets\Pjax;

/* @var $this yii\web\View */
/* @var $searchModel app\modules\admin\modules\g\models\OrderSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = '订单统计';
$this->params['breadcrumbs'][] = $this->title;

$this->params['menus'] = [
    ['label' => Yii::t('app', 'List'), 'url' => ['index']],
];
?>
<div class="order-index">
    <?php Pjax::begin([
        'timeout' => 6000,
    ]); ?>
    <div class="form-outside form-search form-layout-column">
        <div class="order-search form">
            <?php $form = ActiveForm::begin([
                'action' => ['statistics'],
                'method' => 'get',
                'options' => [
                    'data-pjax' => 1
                ],
            ]); ?>
            <div class="entry">
                <?= $form->field($model, 'organization_id')->dropDownList(Option::organizations(), ['prompt' => '']) ?>
            </div>
            <div class="entry">
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
            </div>
            <div class="form-group buttons">
                <?= Html::submitButton('搜索', ['class' => 'btn btn-primary']) ?>
                <?= Html::resetButton('重置', ['class' => 'btn btn-outline-secondary']) ?>
            </div>
            <?php ActiveForm::end(); ?>
        </div>
    </div>
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'columns' => [
            [
                'class' => 'yii\grid\SerialColumn',
                'contentOptions' => ['class' => 'serial-number']
            ],
            [
                'attribute' => 'organization_id',
                'format' => 'organization',
                'header' => '组织',
                'contentOptions' => ['class' => 'organization']
            ],
            [
                'attribute' => 'shop_name',
                'header' => '店铺',
                'contentOptions' => ['style' => 'width: 200px;']
            ],
            [
                'attribute' => 'count',
                'header' => '数量',
                'headerOptions' => ['class' => 'last'],
            ],
        ],
    ]); ?>
    <?php Pjax::end(); ?>
</div>

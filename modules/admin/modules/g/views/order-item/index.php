<?php

use app\modules\admin\components\JsBlock;
use yii\grid\GridView;
use yii\helpers\Url;
use yii\helpers\VarDumper;
use yii\widgets\Pjax;

/* @var $this yii\web\View */
/* @var $searchModel app\modules\admin\modules\g\models\OrderItemSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = '订单商品管理';
$this->params['breadcrumbs'][] = $this->title;

$this->params['menus'] = [
    ['label' => Yii::t('app', 'List'), 'url' => ['index']],
];
?>
<div class="order-item-index">
    <?php Pjax::begin([
        'timeout' => 6000,
    ]); ?>
    <?= $this->render('_search', ['model' => $searchModel]); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'columns' => [
            [
                'class' => 'yii\grid\SerialColumn',
                'contentOptions' => ['class' => 'serial-number']
            ],
            [
                'attribute' => 'order.number',
                'contentOptions' => ['style' => 'width: 120px;']
            ],
//            'product_id',
            [
                'attribute' => 'sku',
                'contentOptions' => ['style' => 'width: 120px;']
            ],
            'product_name',
            [
                'attribute' => 'extend',
                'format' => 'raw',
                'value' => function ($model) {
                    return VarDumper::dumpAsString($model->extend, 10, false);
                },
                'contentOptions' => ['class' => 'break-all']
            ],
            [
                'attribute' => 'quantity',
                'contentOptions' => ['class' => 'number'],
            ],
            //'vendor_id',
            [
                'attribute' => 'sale_price',
                'contentOptions' => ['class' => 'number'],
            ],
            [
                'attribute' => 'cost_price',
                'contentOptions' => ['class' => 'number'],
            ],
            'remark:ntext',
            [
                'attribute' => 'ignored',
                'format' => 'boolean',
                'contentOptions' => ['class' => 'boolean pointer enabled-handler'],
            ],
            [
                'class' => 'yii\grid\ActionColumn',
                'template' => '{view} {delete}',
                'headerOptions' => ['class' => 'buttons-2 last'],
            ],
        ],
    ]); ?>
    <?php Pjax::end(); ?>
</div>
<?php JsBlock::begin() ?>
<script type="text/javascript">
    yadjet.actions.toggle("table td.enabled-handler img", "<?= Url::toRoute('switch') ?>");
</script>
<?php JsBlock::end() ?>


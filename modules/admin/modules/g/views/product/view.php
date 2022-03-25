<?php

use app\models\Member;
use app\modules\admin\modules\g\models\Product;
use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model app\modules\admin\modules\g\models\Product */

$this->title = $model->sku;
$this->params['breadcrumbs'][] = ['label' => '商品管理', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);

$this->params['menus'] = [
    ['label' => Yii::t('app', 'List'), 'url' => ['index']],
    ['label' => Yii::t('app', 'Create'), 'url' => ['create']],
    ['label' => Yii::t('app', 'Update'), 'url' => ['update', 'id' => $model->id]],
];
?>
<div class="product-view">
    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'id',
            'category.name',
            'key',
            'sku',
            'chinese_name',
            'english_name',
            'weight',
            'size_length',
            'size_width',
            'size_height',
            'allow_offset_weight',
            'stock_quantity',
            [
                'attribute' => 'status',
                'format' => 'raw',
                'value' => function ($model) {
                    return Product::statusOptions()[$model->status];
                }
            ],
            'price',
            'cost_price',
            [
                'attribute' => 'image',
                'format' => 'raw',
                'value' => function ($model) {
                    return Html::img($model['image'], ['style' => 'max-width: 400px;']);
                }
            ],
            'development_member_id',
            'purchase_member_id',
            'purchase_reference_price',
            'qc_description',
            'created_at:datetime',
            'updated_at:datetime',
        ],
    ]) ?>
</div>

<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model app\modules\admin\modules\wuliu\models\AmazonOrderItem */

$this->title = $model->product_name;
\yii\web\YiiAsset::register($this);
?>
<link rel="stylesheet" href="http://cdn.bootcss.com/bootstrap/3.3.0/css/bootstrap.min.css">
<div class="amazon-order-item-view">
    <h1><?= Html::encode($this->title) ?></h1>
    <ul class="nav nav-pills" style="margin-bottom: 10px">
        <li role="presentation"><a href="index">亚马逊产品列表</a></li>
        <li role="presentation"><a href="create">添加亚马逊产品</a></li>
        <li role="presentation" class="active"><a href="#">产品详情</a></li>
    </ul>
    <p>
        <?= Html::a('修改', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
        <?= Html::a('删除', ['delete', 'id' => $model->id], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => 'Are you sure you want to delete this item?',
                'method' => 'post',
            ],
        ]) ?>
    </p>
    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'id',
            'order_id',
            'product_name',
            [
                'attribute' => 'product_image',
                'value' => function ($model) {
                    return Html::a(Html::img($model->product_image, ['width' => '40px', 'height' => '40px']), $model->product_image, ['target' => '_blank']);
                },
                'format' => 'raw',
            ],
            'product_quantity',
            'size',
            'color',
            'customized',
            'created_at:datetime',
            'updated_at:datetime',
        ],
    ]) ?>
</div>

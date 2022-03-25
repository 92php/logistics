<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\modules\admin\modules\wuliu\models\AmazonOrderItem */

$this->title = '修改产品: ' . $model->product_name;
$this->params['breadcrumbs'][] = ['label' => 'Amazon Order Items', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<link rel="stylesheet" href="http://cdn.bootcss.com/bootstrap/3.3.0/css/bootstrap.min.css">
<div class="amazon-order-item-update">
    <h1><?= Html::encode($this->title) ?></h1>
    <ul class="nav nav-pills" style="margin-bottom: 10px">
        <li role="presentation"><a href="index">亚马逊产品列表</a></li>
        <li role="presentation"><a href="create">添加亚马逊产品</a></li>
        <li role="presentation" class="active"><a href="#">修改产品</a></li>
    </ul>
    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>
</div>

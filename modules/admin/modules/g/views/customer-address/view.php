<?php

use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model app\modules\admin\modules\g\models\CustomerAddress */

$this->title = $model->id;
$this->params['breadcrumbs'][] = ['label' => '客户地址管理', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);

$this->params['menus'] = [
    ['label' => Yii::t('app', 'List'), 'url' => ['index']],
    ['label' => Yii::t('app', 'Create'), 'url' => ['create']],
    ['label' => Yii::t('app', 'Update'), 'url' => ['update', 'id' => $model->id]],
];
?>
<div class="customer-address-view">
    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'id',
            'key',
            'first_name',
            'last_name',
            'company',
            'address1',
            'address2',
            'country_id',
            'province',
            'city',
            'zip',
            'phone',
        ],
    ]) ?>
</div>

<?php

use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model app\modules\admin\modules\g\models\Package */

$this->title = $model->number;
$this->params['breadcrumbs'][] = ['label' => '包裹管理', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);

$this->params['menus'] = [
    ['label' => Yii::t('app', 'List'), 'url' => ['index']],
    ['label' => Yii::t('app', 'Create'), 'url' => ['create']],
    ['label' => Yii::t('app', 'Update'), 'url' => ['update', 'id' => $model->id]],
];
?>
<div class="package-view">
    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'id',
            'country.abbreviation',
            'shop.name',
            'key',
            'number',
            'waybill_number',
            'weight',
            'freight_cost',
            'delivery_datetime:datetime',
            'logistics_line_id',
            'logistics_query_raw_results:ntext',
            'logistics_last_check_datetime:datetime',
            'status:packageStatus',
            'remark:ntext',
            'created_at:datetime',
            'updated_at:datetime',
        ],
    ]) ?>
</div>

<?php

use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model app\modules\admin\modules\g\models\WarehouseSheet */

$this->title = $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Warehouse Sheets', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);

$this->params['menus'] = [
    ['label' => Yii::t('app', 'List'), 'url' => ['index']],
    ['label' => Yii::t('app', 'Create'), 'url' => ['create']],
    ['label' => Yii::t('app', 'Update'), 'url' => ['update', 'id' => $model->id]],
];
?>
<div class="warehouse-sheet-view">
    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'id',
            'warehouse_id',
            'number',
            'type',
            'method',
            'change_datetime:datetime',
            'operation_member_id',
            'operation_datetime:datetime',
            'remark:ntext',
            'created_at',
            'created_by',
        ],
    ]) ?>
</div>

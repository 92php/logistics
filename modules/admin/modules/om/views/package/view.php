<?php

use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model app\modules\admin\modules\om\models\Package */

$this->title = $model->number;
$this->params['breadcrumbs'][] = ['label' => '包裹列表', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);

$this->params['menus'] = [
    ['label' => Yii::t('app', 'List'), 'url' => ['index']],
    ['label' => Yii::t('app', 'Create'), 'url' => ['create']],
    ['label' => Yii::t('app', 'Update'), 'url' => ['update', 'id' => $model->id]],
]
?>
<div class="package-view">
    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'id',
            'number',
            'title',
            'items_quantity',
            'remaining_items_quantity',
            'created_at:datetime',
            'updated_at:datetime',
        ],
    ]) ?>
</div>

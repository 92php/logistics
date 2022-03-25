<?php

use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model app\modules\admin\modules\g\models\Customer */

$this->title = $model->full_name;
$this->params['breadcrumbs'][] = ['label' => '客户管理', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);

$this->params['menus'] = [
    ['label' => Yii::t('app', 'List'), 'url' => ['index']],
    ['label' => Yii::t('app', 'Create'), 'url' => ['create']],
    ['label' => Yii::t('app', 'Update'), 'url' => ['update', 'id' => $model->id]],
];
?>
<div class="customer-view">
    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'id',
            'platform_id:platform',
            'key',
            'email:email',
            'first_name',
            'last_name',
            'phone',
            'currency',
            'remark:ntext',
            'status:customerStatus',
            'created_at:datetime',
            'updated_at:datetime',
        ],
    ]) ?>
</div>

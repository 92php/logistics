<?php

use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model app\modules\admin\modules\g\models\Tag */

$this->title = $model->name;
$this->params['breadcrumbs'][] = ['label' => '标签管理', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);

$this->params['menus'] = [
    ['label' => Yii::t('app', 'List'), 'url' => ['index']],
    ['label' => Yii::t('app', 'Create'), 'url' => ['create']],
    ['label' => Yii::t('app', 'Update'), 'url' => ['update', 'id' => $model->id]],
];
?>
<div class="tag-view">
    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'id',
            'type',
            'parent_id',
            'name',
            'ordering',
            'enabled',
            'created_at',
            'created_by',
            'updated_at',
            'updated_by',
        ],
    ]) ?>
</div>

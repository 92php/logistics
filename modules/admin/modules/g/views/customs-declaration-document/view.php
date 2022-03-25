<?php

use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model app\modules\admin\modules\g\models\CustomsDeclarationDocument */

$this->title = $model->id;
$this->params['breadcrumbs'][] = ['label' => '报关信息', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);

$this->params['menus'] = [
    ['label' => Yii::t('app', 'List'), 'url' => ['index']],
    ['label' => Yii::t('app', 'Create'), 'url' => ['create']],
    ['label' => Yii::t('app', 'Update'), 'url' => ['update', 'id' => $model->id]],
];
?>
<div class="customs-declaration-document-view">
    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'id',
            'code',
            'chinese_name',
            'english_name',
            'weight',
            'amount',
            'danger_level',
            'default',
            'enabled',
            'created_at',
            'created_by',
            'updated_at',
            'updated_by',
        ],
    ]) ?>
</div>

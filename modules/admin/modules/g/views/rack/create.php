<?php

/* @var $this yii\web\View */
/* @var $model app\modules\admin\modules\g\models\Rack */

$this->title = 'Create Rack';
$this->params['breadcrumbs'][] = ['label' => 'Racks', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

$this->params['menus'] = [
    ['label' => Yii::t('app', 'List'), 'url' => ['index']],
    ['label' => Yii::t('app', 'Create'), 'url' => ['create']],
];
?>
<div class="rack-create">
    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>
</div>

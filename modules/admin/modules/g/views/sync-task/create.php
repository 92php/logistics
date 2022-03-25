<?php

/* @var $this yii\web\View */
/* @var $model app\modules\admin\modules\g\models\SyncTask */

$this->title = '创建任务';
$this->params['breadcrumbs'][] = ['label' => '同步任务管理', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

$this->params['menus'] = [
    ['label' => Yii::t('app', 'List'), 'url' => ['index']],
    ['label' => Yii::t('app', 'Create'), 'url' => ['create']],
];
?>
<div class="sync-task-create">
    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>
</div>

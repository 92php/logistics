<?php

/* @var $this yii\web\View */
/* @var $model app\modules\admin\modules\wuliu\models\CompanyLine */

$this->title = '更新: ' . $model->name;
$this->params['breadcrumbs'][] = ['label' => '物流公司线路管理', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->name, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = '更新';

$this->params['menus'] = [
    ['label' => Yii::t('app', 'List'), 'url' => ['index']],
    ['label' => Yii::t('app', 'Create'), 'url' => ['create']]
];
?>
<div class="company-line-update">
    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>
</div>

<?php

/* @var $this yii\web\View */
/* @var $model app\modules\admin\modules\wuliu\models\CompanyLineRoute */

$this->title = '添加';
$this->params['breadcrumbs'][] = ['label' => '物流线路路由管理', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

$this->params['menus'] = [
    ['label' => Yii::t('app', 'List'), 'url' => ['index']],
    ['label' => Yii::t('app', 'Create'), 'url' => ['create']],
];
?>
<div class="company-line-route-create">
    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>
</div>

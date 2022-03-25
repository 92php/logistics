<?php

/* @var $this yii\web\View */
/* @var $model app\modules\admin\modules\wuliu\models\FreightTemplateFee */

$template = $model->template;
$this->title = $template ? $template->name : '';
$this->params['breadcrumbs'][] = ['label' => '运费模板计费管理', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $template ? $template->name : '', 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = '更新';

$this->params['menus'] = [
    ['label' => Yii::t('app', 'List'), 'url' => ['index']],
    ['label' => Yii::t('app', 'Create'), 'url' => ['create']]
];
?>
<div class="freight-template-fee-update">
    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>
</div>

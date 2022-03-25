<?php

/* @var $this yii\web\View */
/* @var $model app\modules\admin\modules\wuliu\models\FreightTemplateFee */

$this->title = '添加';
$this->params['breadcrumbs'][] = ['label' => '运费模板计费管理', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

$this->params['menus'] = [
    ['label' => Yii::t('app', 'List'), 'url' => ['index']],
    ['label' => Yii::t('app', 'Create'), 'url' => ['create']],
];
?>
<div class="freight-template-fee-create">
    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>
</div>

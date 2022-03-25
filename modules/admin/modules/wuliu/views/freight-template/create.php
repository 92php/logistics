<?php

/* @var $this yii\web\View */
/* @var $model app\modules\admin\modules\wuliu\models\FreightTemplate */

$this->title = '运费模板管理';
$this->params['breadcrumbs'][] = ['label' => '运费模板管理', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

$this->params['menus'] = [
    ['label' => Yii::t('app', 'List'), 'url' => ['index']],
    ['label' => Yii::t('app', 'Create'), 'url' => ['create']],
];
?>
<div class="freight-template-create">
    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>
</div>

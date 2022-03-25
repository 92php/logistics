<?php

/* @var $this yii\web\View */
/* @var $model app\modules\admin\modules\wuliu\models\Company */

$this->title = '添加物流公司';
$this->params['breadcrumbs'][] = ['label' => '物流公司列表', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

$this->params['menus'] = [
    ['label' => Yii::t('app', 'List'), 'url' => ['index']],
    ['label' => Yii::t('app', 'Create'), 'url' => ['create']],
];
?>
<div class="company-create">
    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>
</div>

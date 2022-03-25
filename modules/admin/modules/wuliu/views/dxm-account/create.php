<?php

/* @var $this yii\web\View */
/* @var $model app\modules\admin\modules\wuliu\models\DxmAccount */

$this->title = '添加店小秘账户';
$this->params['breadcrumbs'][] = ['label' => '店小秘账户列表', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

$this->params['menus'] = [
    ['label' => Yii::t('app', 'List'), 'url' => ['index']],
    ['label' => Yii::t('app', 'Create'), 'url' => ['create']],
];
?>
<div class="dxm-account-create">
    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>
</div>

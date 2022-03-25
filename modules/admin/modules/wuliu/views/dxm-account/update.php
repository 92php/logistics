<?php

/* @var $this yii\web\View */
/* @var $model app\modules\admin\modules\wuliu\models\DxmAccount */

$this->title = '修改店小秘账户: ' . $model->username;
$this->params['breadcrumbs'][] = ['label' => '店小秘账户列表', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->username, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = '修改';

$this->params['menus'] = [
    ['label' => Yii::t('app', 'List'), 'url' => ['index']],
    ['label' => Yii::t('app', 'Create'), 'url' => ['create']]
];
?>
<div class="dxm-account-update">
    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>
</div>

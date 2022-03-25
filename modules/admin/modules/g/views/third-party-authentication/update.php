<?php

/* @var $this yii\web\View */
/* @var $model app\modules\admin\modules\g\models\ThirdPartyAuthentication */

$this->title = '更新: ' . $model->name;
$this->params['breadcrumbs'][] = ['label' => '第三方平台认证管理', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->name, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = '更新:';

$this->params['menus'] = [
    ['label' => Yii::t('app', 'List'), 'url' => ['index']],
    ['label' => Yii::t('app', 'Create'), 'url' => ['create']]
];
?>
<div class="third-party-authentication-update">
    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>
</div>

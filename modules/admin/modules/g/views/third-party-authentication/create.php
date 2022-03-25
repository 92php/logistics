<?php

/* @var $this yii\web\View */
/* @var $model app\modules\admin\modules\g\models\ThirdPartyAuthentication */

$this->title = '添加';
$this->params['breadcrumbs'][] = ['label' => '第三方平台认证管理', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

$this->params['menus'] = [
    ['label' => Yii::t('app', 'List'), 'url' => ['index']],
    ['label' => Yii::t('app', 'Create'), 'url' => ['create']],
];
?>
<div class="third-party-authentication-create">
    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>
</div>

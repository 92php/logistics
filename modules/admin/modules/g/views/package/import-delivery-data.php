<?php

use app\modules\admin\components\MessageBox;
use app\modules\admin\widgets\MenuButtons;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\modules\admin\modules\g\models\Package */

$this->title = '导入发货数据';
$this->params['breadcrumbs'][] = ['label' => '包裹管理', 'url' => ['index']];
$this->params['breadcrumbs'][] = '导入发货数据';

$this->params['menus'] = [
    ['label' => Yii::t('app', 'List'), 'url' => ['index']],
    ['label' => Yii::t('app', 'Create'), 'url' => ['create']]
];

?>
<div class="form-outside">
    <div class="package-form form">
        <?php $form = ActiveForm::begin(['options' => ['enctype' => 'multipart/form-data']]); ?>
        <?= $form->field($model, 'files[]')->fileInput([
            'multiple' => true,
            'accept' => '.csv',
        ]) ?>
        <div class="form-group buttons">
            <?= Html::submitButton('导入', ['class' => 'btn btn-success']) ?>
        </div>
        <?php ActiveForm::end() ?>
    </div>
</div>
<?php
$session = Yii::$app->getSession();
if ($session->hasFlash('notice')) {
    echo MessageBox::widget([
        'message' => $session->getFlash('notice'),
    ]);
}
?>

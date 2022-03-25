<?php

use app\modules\admin\components\MessageBox;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

$this->title = 'Excel处理';
$this->registerCssFile("http://cdn.bootcss.com/bootstrap/3.3.0/css/bootstrap.min.css");

?>
    <h1><?= Html::encode($this->title) ?></h1>
    <div class="form-outside">
        <div class="form">
            <?php $form = ActiveForm::begin(['action' => ['upload'], 'method' => 'post']); ?>
            <?= $form->field($model, 'file')->fileInput(['accept' => '.xlsx']) ?>
            <div class="form-group buttons">
                <?= Html::submitButton('提交', ['class' => 'btn btn-success']) ?>
                <?= Html::resetButton('重置', ['class' => 'btn btn-primary', 'name' => 'submit-button']) ?>
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
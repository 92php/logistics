<?php

use yii\helpers\Html;
use yii\web\YiiAsset;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model app\modules\admin\modules\wuliu\models\DxmAccount */

$this->title = $model->username;
$this->params['breadcrumbs'][] = ['label' => '店小秘账户列表', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
YiiAsset::register($this);

$this->params['menus'] = [
    ['label' => Yii::t('app', 'List'), 'url' => ['index']],
    ['label' => Yii::t('app', 'Create'), 'url' => ['create']],
    ['label' => Yii::t('app', 'Update'), 'url' => ['update', 'id' => $model->id]]
];
?>
<div class="dxm-account-view">
    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'id',
            'username',
            [
                'attribute' => 'company_id',
                'value' => function ($model) {
                    $company = $model->company;

                    return $company ? Html::a($company->name, ['company/view', 'id' => $model->company_id]) : '';
                },
                'format' => 'raw',
            ],
            'platform_id:platform',
            'cookies:raw',
            'remark:ntext',
            [
                'attribute' => 'is_valid',
                'format' => 'boolean',
                'contentOptions' => ['class' => 'boolean']
            ],
            'created_at:datetime',
            'updated_at:datetime',
        ],
    ]) ?>
</div>

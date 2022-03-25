<?php

use yii\helpers\Html;
use yii\web\YiiAsset;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model app\modules\admin\modules\wuliu\models\CompanyLine */

$this->title = $model->name;
$this->params['breadcrumbs'][] = ['label' => '物流公司线路管理', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
YiiAsset::register($this);

$this->params['menus'] = [
    ['label' => Yii::t('app', 'List'), 'url' => ['index']],
    ['label' => Yii::t('app', 'Create'), 'url' => ['create']],
    ['label' => Yii::t('app', 'Update'), 'url' => ['update', 'id' => $model->id]]
];
?>
<div class="company-line-view">
    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'id',
            [
                'attribute' => 'company.name',
                'value' => function ($model) {
                    $company = $model->company;

                    return $company ? Html::a($company->name, ['company/view', 'id' => $model->company_id]) : '';
                },
                'format' => 'raw',
            ],
            'name',
            'estimate_days',
            'enabled:boolean',
            'remark:ntext',
            'created_at:datetime',
            'updated_at:datetime',
        ],
    ]) ?>
</div>

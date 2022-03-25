<?php

use app\modules\admin\modules\wuliu\extensions\Formatter;
use yii\helpers\Html;
use yii\web\YiiAsset;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model app\modules\admin\modules\wuliu\models\FreightTemplate */

$this->title = $model->name;
$this->params['breadcrumbs'][] = ['label' => '运费模板管理', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
YiiAsset::register($this);

$this->params['menus'] = [
    ['label' => Yii::t('app', 'List'), 'url' => ['index']],
    ['label' => Yii::t('app', 'Create'), 'url' => ['create']],
    ['label' => Yii::t('app', 'Update'), 'url' => ['update', 'id' => $model->id]]
];
$formatter = new Formatter();
?>
<ul class="tabs-common">
    <li class="active"><a href="javascript:" data-toggle="tab-panel-basic">基本资料</a></li>
    <li><a href="javascript:" data-toggle="tab-panel-fee">计费详情</a></li>
</ul>
<div class="panels">
    <div class="tab-panel" id="tab-panel-basic">
        <div class="freight-template-view">
            <?= DetailView::widget([
                'model' => $model,
                'attributes' => [
                    'name',
                    [
                        'label' => '物流公司名称',
                        'attribute' => 'company.name',
                        'value' => function ($model) {
                            $company = $model->company;

                            return $company ? Html::a($company->name, ['company/view', 'id' => $model->company_id]) : '';
                        },
                        'format' => 'raw'
                    ],
                    'fee_mode:feeMode',
                    [
                        'attribute' => 'enabled',
                        'format' => 'boolean',
                        'contentOptions' => ['class' => 'boolean']
                    ],
                    'remark:ntext',
                    [
                        'attribute' => 'created_at',
                        'format' => 'datetime',
                        'contentOptions' => ['class' => 'datetime'],
                    ],
                    [
                        'attribute' => 'updated_at',
                        'format' => 'datetime',
                        'contentOptions' => ['class' => 'datetime'],
                    ],
                ],
            ]) ?>
        </div>
    </div>
    <div class="tab-panel" id="tab-panel-fee" style="display: none">
        <div class="grid-view">
            <table class="table table-striped table-bordered">
                <thead>
                <tr>
                    <th class="serial-number">序号</th>
                    <th>所属线路名称</th>
                    <th class="number">重量范围</th>
                    <th class="number">首重/首重费用</th>
                    <th class="number">续重单位重量/续重费用</th>
                    <th class="number">挂号费</th>
                    <th class="text">备注</th>
                    <th class="boolean last">激活</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($model->templateFees as $i => $fee): ?>
                    <tr>
                        <td class="serial-number"><?= Html::a($fee['id'], ['freight-template-fee/view', 'id' => $fee['id']]) ?></td>
                        <td style="width: 200px"><?php
                            $line = $fee->line;
                            echo $line ? Html::a($line->name, ['company-line/view', 'id' => $line->id]) : '';
                            ?>
                        </td>
                        <td class="number"><?= $fee['min_weight'] . 'g-' . $fee['max_weight'] . 'g' ?></td>
                        <td class="number"><?= $fee['first_weight'] . 'g/￥' . $fee['first_fee'] ?></td>
                        <td class="number"><?= $fee['continued_weight'] . 'g/￥' . $fee['continued_fee'] ?></td>
                        <td class="number"><?= '￥' . $fee['base_fee'] ?></td>
                        <td class="text"><?= $fee['remark'] ?></td>
                        <td class="boolean"><?= $formatter->asBoolean($fee['enabled']) ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php

use yii\web\YiiAsset;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model app\modules\admin\modules\g\models\Shop */

$this->title = $model->name;
$this->params['breadcrumbs'][] = ['label' => '店铺管理', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
YiiAsset::register($this);

$this->params['menus'] = [
    ['label' => Yii::t('app', 'List'), 'url' => ['index']],
    ['label' => Yii::t('app', 'Create'), 'url' => ['create']],
    ['label' => Yii::t('app', 'Update'), 'url' => ['update', 'id' => $model->id]]
];
$configurations = ($model->thirdPartyAuthentication && $model->thirdPartyAuthentication->configurations) ? $model->thirdPartyAuthentication->configurations : [];
?>
<div class="shop-view">
    <ul class="tabs-common">
        <li class="active"><a href="javascript:;" data-toggle="tab-panel-basic">基本资料</a></li>
        <?php
        if ($configurations):
            foreach ($configurations as $key => $item):
                ?>
                <li><a href="javascript:;" data-toggle="tab-panel-configurations-<?= $key ?>"><?= $key ?> 配置信息</a></li>
            <?php
            endforeach;
        endif;
        ?>
        <?php if ($metaItems): ?>
            <li><a href="javascript:;" data-toggle="tab-panel-meta">扩展资料</a></li>
        <?php endif; ?>
    </ul>
    <div class="panels">
        <div class="tab-panel" id="tab-panel-basic">
            <?= DetailView::widget([
                'model' => $model,
                'attributes' => [
                    'id',
                    'organization_id:organization',
                    'platform_id:platform',
                    'name',
                    'url:url',
                    'product_type:productType',
                    [
                        'attribute' => 'thirdPartyAuthentication.name',
                    ],
                    'third_party_sign',
                    'enabled:boolean',
                    'remark:ntext',
                    'created_at:datetime',
                    'updated_at:datetime',
                ],
            ]) ?>
        </div>
        <?php
        if ($configurations):
            foreach ($configurations as $key => $item):
                ?>
                <div class="tab-panel" id="tab-panel-configurations-<?= $key ?>" style="display: none;">
                    <div class="grid-view">
                        <table class="table table-striped table-bordered">
                            <thead>
                            <tr>
                                <th>#</th>
                                <th>标签</th>
                                <th class="last">值</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php
                            $i = 0;
                            foreach ($item as $kk => $vv):
                                $i++;
                                ?>
                                <tr>
                                    <td class="serial-number"><?= $i ?></td>
                                    <td style="width: 120px;"><?= $kk ?></td>
                                    <td>
                                        <div class="break-all">
                                            <?= $vv ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php
            endforeach;
        endif;
        ?>
        <?php if ($metaItems): ?>
            <div class="tab-panel" id="tab-panel-meta" style="display: none;">
                <div class="grid-view">
                    <table class="table table-striped table-bordered">
                        <thead>
                        <tr>
                            <th>#</th>
                            <th>标签</th>
                            <th class="last">值</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php
                        $i = 0;
                        foreach ($metaItems as $item):
                            $i++;
                            ?>
                            <tr>
                                <td class="serial-number"><?= $i ?></td>
                                <td style="width: 120px;"><?= $item['label'] ?></td>
                                <td>
                                    <div class="break-all">
                                        <?= $item['value'] ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>


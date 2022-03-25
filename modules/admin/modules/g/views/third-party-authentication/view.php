<?php

use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model app\modules\admin\modules\g\models\ThirdPartyAuthentication */

$this->title = $model->name;
$this->params['breadcrumbs'][] = ['label' => '第三方平台认证管理', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);

$this->params['menus'] = [
    ['label' => Yii::t('app', 'List'), 'url' => ['index']],
    ['label' => Yii::t('app', 'Create'), 'url' => ['create']],
    ['label' => Yii::t('app', 'Update'), 'url' => ['update', 'id' => $model->id]]
];
$configurations = $model->getConfigurations();
?>
<div class="third-party-authentication-view">
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
    </ul>
    <div class="panels">
        <div class="tab-panel" id="tab-panel-basic">
            <?= DetailView::widget([
                'model' => $model,
                'attributes' => [
                    'id',
                    'platform_id:thirdPartyPlatform',
                    'name',
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
    </div>
</div>

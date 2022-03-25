<?php

use app\modules\admin\components\JsBlock;
use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model app\modules\admin\modules\wuliu\models\Company */

$this->title = $model->name;
$this->params['breadcrumbs'][] = ['label' => '物流公司列表', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);

$this->params['menus'] = [
    ['label' => Yii::t('app', 'List'), 'url' => ['index']],
    ['label' => Yii::t('app', 'Create'), 'url' => ['create']],
    ['label' => Yii::t('app', 'Update'), 'url' => ['update', 'id' => $model->id]]
];
$baseUrl = Yii::$app->getRequest()->getBaseUrl() . '/admin';
/* @var $formatter \app\modules\admin\modules\wuliu\extensions\Formatter */
$formatter = Yii::$app->getFormatter();
?>
<ul class="tabs-common">
    <li class="active"><a href="javascript:;" data-toggle="tab-panel-basic">基本资料</a></li>
    <li><a href="javascript:;" data-toggle="tab-panel-line-routes">线路路由</a></li>
</ul>
<div class="panels">
    <div class="tab-panel" id="tab-panel-basic">
        <div class="company-view">
            <?= DetailView::widget([
                'model' => $model,
                'attributes' => [
                    'id',
                    'code',
                    'name',
                    'website_url:url',
                    'linkman',
                    'mobile_phone',
                    'enabled:boolean',
                    'remark:ntext',
                    'created_at:datetime',
                    'updated_at:datetime',
                ],
            ]) ?>
        </div>
    </div>
    <div class="tab-panel" id="tab-panel-line-routes" style="display: none">
        <?=
        \yii\grid\GridView::widget([
            'id' => 'grid-table-categories',
            'dataProvider' => $dataProvider,
            'rowOptions' => function ($model, $key, $index, $grid) {
                return [
                    'id' => 'row-' . $model['id'],
                    'data-tt-id' => $model['id'],
                    'class' => $model['enabled'] ? 'enabled' : 'disabled',
                    'data-tt-parent-id' => $model['parent_id'],
                    'style' => $model['parent_id'] ? 'display: none' : '',
                ];
            },
            'columns' => [
                [
                    'attribute' => 'name',
                    'header' => '名称',
                    'format' => 'raw',
                    'value' => function ($model) {
                        $isParent = $model['parent_id'] == 0;
                        if ($isParent) {
                            $url = ['company-line/update', 'id' => $model['id']];
                        } else {
                            $index = strpos($model['id'], '.');

                            $url = ['company-line-route/update', 'id' => substr($model['id'], $index + 1)];
                        }
                        if ($isParent) {
                            return Html::a($model['name'], $url);
                        } else {
                            return "<span style='display: inline-block; border-radius: 20px; width: 20px; height: 20px; background-color: red; color: white; margin-right: 10px; text-align: center'>{$model['step']}</span>" . Html::a($model['name'], $url);
                        }
                    },
                    'contentOptions' => ['class' => 'wrap'],
                ],
                [
                    'attribute' => 'detection_keyword',
                    'header' => '检测条件',
                    'value' => function ($model) {
                        $text = null;
                        if ($model['parent_id'] != 0) {
                            $text = $model['detection_keyword'];
                        }

                        return $text;
                    },
                    'contentOptions' => ['class' => 'wrap'],
                ],
                [
                    'attribute' => 'estimate_days',
                    'header' => '预计天数',
                    'value' => function ($model) {
                        $text = null;
                        if ($model['parent_id']) {
                            $text = $model['estimate_days'] ? "{$model['estimate_days']} 天" : '自动估算';
                        }

                        return $text;
                    },
                    'contentOptions' => ['class' => 'number'],
                ],
                [
                    'attribute' => 'package_status',
                    'header' => '包裹状态',
                    'value' => function ($model) use ($formatter) {
                        $text = null;
                        if ($model['package_status']) {
                            $text = $formatter->asPackageStatus($model['package_status']);
                        }

                        return $text;
                    },
                    'contentOptions' => ['class' => 'number'],
                ],
                [
                    'attribute' => 'enabled',
                    'header' => '激活',
                    'format' => 'raw',
                    'value' => function ($model) use ($formatter) {
                        $text = null;
                        if ($model['parent_id']) {
                            $text = $formatter->asBoolean($model['enabled']);
                        }

                        return $text;
                    },
                    'headerOptions' => ['class' => 'last'],
                    'contentOptions' => ['class' => 'boolean pointer enabled-handler'],
                ],
            ],
        ]);
        ?>
    </div>
</div>
<?php
$baseUrl = Yii::$app->getRequest()->getBaseUrl() . '/admin/jquery-treetable-3.2.0';
$this->registerCssFile($baseUrl . '/css/jquery.treetable.css');
$this->registerCssFile($baseUrl . '/css/jquery.treetable.theme.default.css');
$this->registerJsFile($baseUrl . '/jquery.treetable.js', [
    'depends' => ['\yii\web\JqueryAsset']
]);

JsBlock::begin();
?>
<script type="text/javascript">
    $("#grid-table-categories table.table").treetable({ expandable: true, initialState: "expand" });
</script>
<?php JsBlock::end() ?>


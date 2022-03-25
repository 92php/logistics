<?php

use app\modules\admin\components\JsBlock;
use app\modules\admin\components\MessageBox;
use app\modules\admin\modules\g\extensions\Formatter;
use yii\grid\GridView;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\Pjax;

/* @var $this yii\web\View */
/* @var $searchModel app\modules\admin\modules\g\models\ShopSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = '店铺管理';
$this->params['breadcrumbs'][] = $this->title;

$this->params['menus'] = [
    ['label' => Yii::t('app', 'List'), 'url' => ['index']],
    ['label' => Yii::t('app', 'Create'), 'url' => ['create']],
    ['label' => '批量添加同步任务', 'url' => 'javascript:;', 'htmlOptions' => ['class' => 'btn-add-sync-task']],
];
/* @var $formatter Formatter */
$formatter = Yii::$app->getFormatter();
$session = Yii::$app->getSession();
?>
<?php
if ($session->hasFlash('notice')) {
    echo MessageBox::widget([
        'message' => $session->getFlash('notice'),
    ]);
}
?>
<div class="shop-index">
    <?php Pjax::begin(); ?>
    <?= $this->render('_search', ['model' => $searchModel]); ?>

    <?= GridView::widget([
        'id' => 'grid-view-shops',
        'dataProvider' => $dataProvider,
        'columns' => [
            [
                'class' => 'yii\grid\CheckboxColumn',
                'contentOptions' => ['class' => 'checkbox-column']
            ],
            [
                'class' => 'yii\grid\SerialColumn',
                'contentOptions' => ['class' => 'serial-number']
            ],
            [
                'attribute' => 'organization_id',
                'format' => 'organization',
                'contentOptions' => ['class' => 'organization'],
            ],
            [
                'attribute' => 'platform_id',
                'format' => 'platform',
                'contentOptions' => ['class' => 'platform'],
            ],
            [
                'attribute' => 'name',
                'format' => 'raw',
                'value' => function ($model) {
                    return "[ #{$model->id} ] " . Html::a($model->name, $model->url, ['target' => '_blank']);
                },
            ],
            [
                'attribute' => 'product_type',
                'format' => 'productType',
                'contentOptions' => ['style' => 'width: 60px; text-align: center;'],
            ],
            [
                'attribute' => 'thirdPartyAuthentication.name',
                'value' => function ($model) use ($formatter) {
                    if ($model->thirdPartyAuthentication) {
                        return sprintf("[ %s ] %s", $formatter->asThirdPartyPlatform($model['thirdPartyAuthentication']['platform_id']), $model['thirdPartyAuthentication']['name']);
                    } else {
                        return null;
                    }
                },
                'contentOptions' => ['style' => 'width: 200px;'],
            ],
            'third_party_sign',
            [
                'attribute' => 'enabled',
                'format' => 'boolean',
                'contentOptions' => ['class' => 'boolean pointer enabled-handler'],
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
            [
                'class' => 'yii\grid\ActionColumn',
                'template' => '{view} {update} {delete}',
                'headerOptions' => ['class' => 'buttons-3 last'],
            ],
        ],
    ]); ?>
    <?php Pjax::end(); ?>
</div>
<?php JsBlock::begin() ?>
<script type="text/javascript">
    yadjet.actions.toggle("table td.enabled-handler img", "<?= Url::toRoute('switch') ?>");
    $('.btn-add-sync-task').on('click', function() {
        var ids = $('#grid-view-shops').yiiGridView('getSelectedRows'),
            url = '<?= Url::toRoute(['add-sync-task', 'shopIds' => '_shopIds']) ?>';
        if (ids.length) {
            url = url.replace('_shopIds', ids.toString());
            window.location.href = url;
        } else {
            layer.alert('请选择您要添加任务的站点。');
        }

        return false;
    });
</script>
<?php JsBlock::end() ?>

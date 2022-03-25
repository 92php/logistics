<?php

use app\modules\admin\components\JsBlock;
use yii\grid\GridView;
use yii\helpers\Url;
use yii\widgets\Pjax;

/* @var $this yii\web\View */
/* @var $searchModel app\modules\admin\modules\g\models\VendorSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = '供应商管理';
$this->params['breadcrumbs'][] = $this->title;

$this->params['menus'] = [
    ['label' => Yii::t('app', 'List'), 'url' => ['index']],
    ['label' => Yii::t('app', 'Create'), 'url' => ['create']],
];
?>
<div class="vendor-index">
    <?php Pjax::begin(); ?>
    <?= $this->render('_search', ['model' => $searchModel]); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'columns' => [
            [
                'class' => 'yii\grid\SerialColumn',
                'contentOptions' => ['class' => 'serial-number']
            ],
            [
                'attribute' => 'name',
//                'contentOptions' => ['style' => 'width: 200px']
            ],
            [
                'attribute' => 'linkman',
                'contentOptions' => ['class' => 'username']
            ],
            [
                'attribute' => 'mobile_phone',
                'contentOptions' => ['class' => 'mobile-phone']
            ],
            [
                'attribute' => 'tel',
                'contentOptions' => ['class' => 'tel']
            ],
            'address',
            [
                'attribute' => 'receipt_duration',
                'contentOptions' => ['class' => 'number'],
            ],
            [
                'attribute' => 'production',
                'contentOptions' => ['class' => 'number'],
            ],
            [
                'attribute' => 'credibility',
                'contentOptions' => ['class' => 'number'],
            ],
            [
                'attribute' => 'member_ids',
                'value' => function ($model) {
                    $names = [];
                    foreach ($model['members'] as $member) {
                        $names[] = $member['username'];
                    }

                    return implode('、', $names);
                },
            ],
            [
                'attribute' => 'enabled',
                'format' => 'boolean',
                'contentOptions' => ['class' => 'boolean pointer enabled-handler']
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
</script>
<?php JsBlock::end() ?>

<?php

use app\modules\admin\components\JsBlock;
use yii\grid\GridView;
use yii\helpers\Url;
use yii\widgets\Pjax;

/* @var $this yii\web\View */
/* @var $searchModel app\modules\admin\modules\wuliu\models\CountrySearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = '国家管理';
$this->params['breadcrumbs'][] = $this->title;

$this->params['menus'] = [
    ['label' => Yii::t('app', 'List'), 'url' => ['index']],
    ['label' => Yii::t('app', 'Create'), 'url' => ['create']],
];
?>
<div class="country-index">
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
                'attribute' => 'region_id',
                'format' => 'countryRegion',
                'contentOptions' => ['style' => 'width: 80px; text-align: center'],
            ],
            [
                'attribute' => 'abbreviation',
                'contentOptions' => ['style' => 'width: 30px; text-align: center'],
            ],
            [
                'attribute' => 'chinese_name',
                'contentOptions' => ['style' => 'width: 160px'],
            ],
            'english_name',
            [
                'attribute' => 'enabled',
                'format' => 'boolean',
                'contentOptions' => ['class' => 'boolean pointer enabled-handler'],
            ],
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
                'template' => '{update} {delete}',
                'headerOptions' => ['class' => 'buttons-2 last'],
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

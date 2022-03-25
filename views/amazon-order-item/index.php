<?php

use app\components\JsBlock;
use yii\grid\GridView;
use yii\helpers\Html;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $searchModel app\modules\admin\modules\wuliu\models\AmazonOrderItemSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = '亚马逊产品列表';
$this->registerCssFile("http://cdn.bootcss.com/bootstrap/3.3.0/css/bootstrap.min.css");
?>
<div class="amazon-order-item-index">
    <h1><?= Html::encode($this->title) ?></h1>
    <?= $this->render('_search', ['model' => $searchModel]); ?>
    <a href="<?= Url::toRoute('create') ?>">
        <button class="btn btn-dark" style="float: right; margin-bottom: 10px">添加亚马逊产品</button>
    </a>
    <button class="btn btn-dark" style="float: right; margin-right: 10px" onclick="upload()">导入excel文件</button>
    <button class="btn btn-dark" style="margin-right: 10px; float: right" onclick="toExcel()">导出excel文件</button>
    <?php $form = \yii\widgets\ActiveForm::begin(['options' => ['enctype' => 'multipart/form-data', 'id' => 'formUpload'], 'action' => 'upload-excel']) ?>
    <?= Html::fileInput('file', '', ['style' => 'display: none', 'id' => 'fileUpload', 'onchange' => 'submit()']) ?>
    <?php \yii\widgets\ActiveForm::end() ?>
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'columns' => [
            [
                'attribute' => '',
                'format' => 'raw',
                'label' => "全选",
                'headerOptions' => ['width' => '50', 'style' => 'cursor:pointer'],
                'contentOptions' => ['align' => 'center'],
                'header' => "<button class='btn btn-default' title='全选' id='all-check'>全选</button>",
                'value' => function ($data) {
                    return "<input type='checkbox' class='i-checks' value={$data['id']}>";
                },
            ],
            [
                'class' => 'yii\grid\SerialColumn',
                'contentOptions' => ['class' => 'serial-number']
            ],
            'order_id',
            'product_name',
            [
                'attribute' => 'product_image',
                'value' => function ($model) {
                    return Html::a(Html::img($model->product_image, ['width' => '40px', 'height' => '40px']), $model->product_image, ['target' => '_blank']);
                },
                'format' => 'raw',
            ],
            'product_quantity',
            'size',
            'color',
            [
                'attribute' => 'customized',
                'format' => 'ntext',
            ],
            'remark:ntext',
            [
                'attribute' => 'created_at',
                'format' => 'datetime',
                'contentOptions' => ['class' => 'datetime']
            ],
            [
                'attribute' => 'updated_at',
                'format' => 'datetime',
                'contentOptions' => ['class' => 'datetime']
            ],

            [
                'class' => 'yii\grid\ActionColumn',
                'template' => '{print} {view} {update} {delete}',
                'header' => '操作',
                'buttons' => [
                    'print' => function ($url, $model, $key) {
                        $icon = Html::tag('button', Html::tag('span', '', ['class' => "glyphicon glyphicon-print", 'aria-hidden' => 'true']), ['class' => 'btn btn-primary']);

                        return Html::a($icon, 'javascript:;', ['onclick' => 'printOrderItem(this, ' . $model->id . ');', 'title' => '打印条码']);
                    },
                    'view' => function ($url, $model, $key) {
                        $icon = Html::tag('button', Html::tag('span', '', ['class' => "glyphicon glyphicon-eye-open", 'aria-hidden' => 'true']), ['class' => 'btn btn-primary']);

                        return Html::a($icon, $url, ['title' => '查看']);
                    },
                    'update' => function ($url, $model, $key) {
                        $icon = Html::tag('button', Html::tag('span', '', ['class' => "glyphicon glyphicon-pencil", 'aria-hidden' => 'true']), ['class' => 'btn btn-primary']);

                        return Html::a($icon, $url, ['title' => '修改']);
                    },
                    'delete' => function ($url, $model, $key) {
                        $icon = Html::tag('button', Html::tag('span', '', ['class' => "glyphicon glyphicon-trash", 'aria-hidden' => 'true']), ['class' => 'btn btn-primary']);

                        return Html::a($icon, $url, ['title' => '删除', 'data' => [
                            'confirm' => 'Are you sure you want to delete this item?',
                            'method' => 'post',
                        ],]);
                    },
                ],
            ],
        ],
    ]); ?>
</div>
<?php JsBlock::begin() ?>
<script type="text/javascript">
    function printOrderItem(e, id) {
        // console.info("OrderItemId: " + id);
        var xhr = new XMLHttpRequest();
        var url = '<?= Url::toRoute(['print', 'id' => '_id']) ?>';
        url = url.replace("_id", id);
        xhr.open("get", url);
        xhr.send();
        xhr.onreadystatechange = function() {
            if (xhr.readyState == 4 && xhr.status == 200) {
                // console.log("response:" + xhr.responseText);
                window.open(xhr.responseText);
            }
        }
    }

    function upload() {
        document.getElementById('fileUpload').click();
    }

    function submit() {
        document.getElementById('formUpload').submit();

    }

    function toExcel() {
        var checked = $('.i-checks:checked');
        var ids = [];
        checked.each(function() {
            ids.push($(this).val())
        });
        var url = '<?= Url::toRoute(['to-excel', 'ids' => '_ids'])?>';
        if (ids.length != 0) {
            url = url.replace("_ids", ids.join(','));
            // console.log(url);
        } else {
            // 未选中导出全部
            url = url.replace("_ids", '');
        }
        window.open(url, '_blank');

    }

    $('#all-check').click(function() {
        $('.i-checks').prop("checked", true);
    });
</script>
<?php JsBlock::end() ?>

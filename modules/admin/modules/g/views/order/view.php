<?php

use app\modules\admin\components\MessageBox;
use app\modules\admin\modules\om\extensions\Formatter;
use yii\helpers\Html;
use yii\helpers\VarDumper;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model app\modules\admin\modules\g\models\Order */

$this->title = $model->number;
$this->params['breadcrumbs'][] = ['label' => '订单列表', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);

$this->params['menus'] = [
    ['label' => Yii::t('app', 'List'), 'url' => ['index']],
];
$formatter = new Formatter();
?>
<div>
    <ul class="tabs-common">
        <li class="active"><a href="javascript:;" data-toggle="tab-panel-basic">基本资料</a></li>
        <li><a href="javascript:;" data-toggle="tab-panel-detail">订单内商品详情</a></li>
        <li><a href="javascript:;" data-toggle="tab-panel-route">商品路由详情</a></li>
    </ul>
    <div class="panels">
        <div class="tab-panel" id="tab-panel-basic">
            <div class="order-view">
                <?= DetailView::widget([
                    'model' => $model,
                    'attributes' => [
                        'id',
                        'key',
                        'number',
                        'type:orderType',
                        'consignee_name',
                        'consignee_mobile_phone',
                        'consignee_tel',
                        [
                            'attribute' => 'country.chinese_name',
                            'label' => '发往国家',
                        ],
                        'consignee_state',
                        'consignee_city',
                        'consignee_address1',
                        'consignee_address2',
                        'consignee_postcode',
                        'total_amount',
                        'status:orderStatus',
                        'platform_id:platform',
                        [
                            'attribute' => 'shop.name',
                            'value' => function ($model) {
                                $shop = $model->shop;

                                return Html::a($shop ? $shop->name : '', ['shop/view', 'id' => $model->shop_id]);
                            },
                            'format' => 'raw',
                        ],
                        'product_type:productType',
                        'third_party_platform_id:thirdPartyPlatform',
                        [
                            'attribute' => 'third_party_platform_status',
                            'value' => function ($model) use ($formatter) {
                                return $formatter->asThirdPlatformOrderStatus($model->third_party_platform_status, $model->third_party_platform_id);
                            },
                        ],
                        'place_order_at:datetime',
                        'payment_at:datetime',
                        'cancelled_at:datetime',
                        'cancel_reason',
                        'closed_at:datetime',
                        'remark:ntext',
                        'created_at:datetime',
                        'updated_at:datetime',
                    ],
                ]) ?>
            </div>
        </div>
        <div class="tab-panel" id="tab-panel-detail" style="display: none">
            <?php if ($model->items): ?>
                <div class="grid-view">
                    <table class="table table-striped table-bordered">
                        <thead>
                        <tr>
                            <th>编号</th>
                            <th>SKU</th>
                            <th>产品名称</th>
                            <th>数量</th>
                            <th>定制信息</th>
                            <th>材质</th>
                            <th>颜色</th>
                            <th>尺寸</th>
                            <th>珠子数量</th>
                            <th>礼盒</th>
                            <th>其他</th>
                            <th>售价</th>
                            <th>成本价</th>
                            <th>供应商</th>
                            <th class="last">状态</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($model->items as $i => $item): ?>
                            <tr>
                                <td class="serial-number"><?= $item->id ?></td>
                                <td class="sku">
                                    <?= Html::tag($item->ignored ? 'del' : 'span', $item->sku) ?>a
                                </td>
                                <td>
                                    <div style="line-height: 36px;">
                                        <?= Html::a(Html::img($item->image, ['width' => 30, 'height' => 30, 'style' => 'float: left; margin-right: 10px; border-radius: 6px']), $item->image, ['target' => '_blank']) ?>
                                        <?= $item->product_name ?>
                                    </div>
                                </td>
                                <td class="number"><?= $item->quantity ?></td>
                                <td>
                                    <div style="overflow-y: auto; line-height: 20px;">
                                        <?= isset($item->extend['names']) ? implode(', ', $item->extend['names']) : '' ?>
                                    </div>
                                </td>
                                <td><?= isset($item->extend['material']) ? $item->extend['material'] : '' ?></td>
                                <td><?= isset($item->extend['color']) ? $item->extend['color'] : '' ?></td>
                                <td><?= isset($item->extend['size']) ? $item->extend['size'] : '' ?></td>
                                <td class="number"><?= isset($item->extend['beads']) ? $item->extend['beads'] : 0 ?></td>
                                <td class="boolean"><?= isset($item->extend['giftBox']) && $item['extend']['giftBox'] ? '√' : '×' ?></td>
                                <td>
                                    <div style="overflow-y: auto; line-height: 20px;">
                                        <?php
                                        $other = isset($item->extend['other']) ? $item->extend['other'] : [];
                                        $content = [];
                                        foreach ($other as $key => $value) {
                                            $content[] = $key . " : " . $value;
                                        }
                                        echo implode("\r\n", $content);
                                        ?>
                                    </div>
                                </td>
                                <td class="price"><?= $item->sale_price ?></td>
                                <td class="price"><?= $item->cost_price ?></td>
                                <td>
                                    <?php
                                    $vendor = $item->vendor;
                                    echo $vendor ? Html::a($vendor->name, ['vendor/view', 'id' => $vendor->id]) : '';
                                    ?>
                                </td>
                                <td><?= $formatter->asOrderItemBusinessStatus($item->business->status) ?></td>
                            </tr>
                            <tr>
                                <td colspan="4"></td>
                                <td colspan="11">
                                    <?= VarDumper::dumpAsString($item['extend'], 10, true) ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <?= MessageBox::widget([
                    'message' => '暂无内容',
                ]) ?>
            <?php endif; ?>
        </div>
        <div class="tab-panel" id="tab-panel-route" style="display: none">
            <div class="grid-view">
                <table class="table table-striped table-bordered">
                    <thead>
                    <tr>
                        <th>节点名称</th>
                        <th>是否到达</th>
                        <th>到达时间</th>
                        <th>是否已取消</th>
                        <th>是否已拒接</th>
                        <th>是否超时</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($model->items as $item): ?>
                        <tr>
                            <td style="color: red;font-size: 16px">商品名： <?= $item->product_name ?></td>
                        </tr>
                        <?php foreach ($item->workflow[0]['steps'] as $step): ?>
                            <tr>
                                <td><?= $step['title'] ?></td>
                                <td class="boolean"><?= $formatter->asBoolean($step['arrive_node']) ?></td>
                                <td class="boolean"><?= $step['datetime'] ? $formatter->asDatetime($step['datetime']) : '暂未到达' ?></td>
                                <td><?= $formatter->asBoolean($step['cancel']) ?></td>
                                <td class="boolean"><?= $formatter->asBoolean($step['reject']) ?></td>
                                <td class="boolean"><?= $formatter->asBoolean($step['overtime']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

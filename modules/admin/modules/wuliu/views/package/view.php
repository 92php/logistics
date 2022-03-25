<?php

use app\modules\admin\components\MessageBox;
use app\modules\admin\modules\wuliu\models\PackageRoute;
use app\modules\api\modules\wuliu\extensions\Formatter;
use yii\helpers\Html;
use yii\web\YiiAsset;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model app\modules\admin\modules\wuliu\models\Package */

$this->title = $model->package_number;
$this->params['breadcrumbs'][] = ['label' => '包裹列表', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
YiiAsset::register($this);

$this->params['menus'] = [
    ['label' => Yii::t('app', 'List'), 'url' => ['index']],
];
/* @var $formatter Formatter */
$formatter = Yii::$app->getFormatter();
?>
<ul class="tabs-common">
    <li class="active"><a href="javascript:" data-toggle="tab-panel-basic">包裹资料</a></li>
    <li><a href="javascript:" data-toggle="tab-panel-logistics-query-results">物流查询结果</a></li>
    <li><a href="javascript:" data-toggle="tab-panel-company-line-routes">线路路由设置</a></li>
    <li><a href="javascript:" data-toggle="tab-panel-route">路由监测结果<?= $model->last_check_datetime ? "【{$formatter->asDatetime($model->last_check_datetime)}】" : '' ?></a></li>
</ul>
<div class="panels">
    <div class="tab-panel" id="tab-panel-basic">
        <div class="package-view">
            <?= DetailView::widget([
                'model' => $model,
                'attributes' => [
                    'id',
                    'package_id',
                    'package_number',
                    'order_number',
                    [
                        'attribute' => 'line.name',
                        'format' => 'raw',
                        'value' => function ($model) {
                            return Html::a($model['line']['name'], ['company-line-route/index', 'CompanyLineRouteSearch[line_id]' => $model['line']['id']], ['target' => '_blank']);
                        },
                    ],
                    'waybill_number',
                    'country.chinese_name',
                    'weight',
                    'freight_cost',
                    'dxmAccount.username',
                    'shop_name',
                    'delivery_datetime:datetime',
                    'estimate_days',
                    'final_days',
                    'sync_status:packageSyncStatus',
                    'status:packageStatus',
                    'remark:ntext',
                    'created_at:datetime',
                    'updated_at:datetime',
                ],
            ]) ?>
        </div>
    </div>
    <div class="tab-panel" id="tab-panel-company-line-routes" style="display: none">
        <?php
        $companyLineRoutes = $model->line ? $model->line->routes : [];
        if ($companyLineRoutes):
            ?>
            <div class="grid-view">
                <table class="table table-striped table-bordered">
                    <thead>
                    <tr>
                        <th class="serial-number">步骤</th>
                        <th class="datetime">事件</th>
                        <th class="datetime">判断依据</th>
                        <th class="number">预计天数</th>
                        <th class="datetime">包裹状态</th>
                        <th class="last">激活</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($companyLineRoutes as $route): ?>
                        <tr>
                            <td class="serial-number"><?= $route['step'] ?></td>
                            <td style="width: 120px"><?= $route['event'] ?></td>
                            <td><?= $route['detection_keyword'] ?></td>
                            <td class="number">
                                <?php
                                $days = $route['estimate_days'];
                                if ($days == 0) {
                                    echo '自动估算';
                                } else if ($days == -1) {
                                    echo '不进行估算';
                                } else {
                                    echo "$days 天";
                                }
                                ?>
                            </td>
                            <td style="width: 80px; text-align: center"><?= $formatter->asPackageStatus($route['package_status']) ?></td>
                            <td class="boolean"><?= $formatter->asBoolean($route['enabled']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php
        else:
            echo MessageBox::widget([
                'message' => '暂无设置',
            ]);
            ?>
        <?php endif; ?>
    </div>
    <div class="tab-panel" id="tab-panel-logistics-query-results" style="display: none">
        <?php
        $results = json_decode($model->logistics_query_raw_results, true);
        if ($results):
            ?>
            <div class="grid-view">
                <table class="table table-striped table-bordered">
                    <thead>
                    <tr>
                        <th class="serial-number">序号</th>
                        <th class="datetime">时间</th>
                        <th>状态</th>
                        <th class="last">描述</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($results as $i => $result): ?>
                        <tr>
                            <td class="serial-number"><?= $i + 1 ?></td>
                            <td class="datetime"><?= $result['datetime'] ?></td>
                            <td style="width: 80px; text-align: center"><?= $result['status'] ?></td>
                            <td><?= $result['description'] ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php
        else:
            echo MessageBox::widget([
                'message' => '暂无物流信息',
            ]);
        endif;
        ?>
    </div>
    <div class="tab-panel" id="tab-panel-route" style="display: none">
        <div class="grid-view">
            <table class="table table-striped table-bordered">
                <thead>
                <tr>
                    <th class="serial-number">序号</th>
                    <th>路由点</th>
                    <th class="datetime">开始时间</th>
                    <th class="number">允许天数</th>
                    <th class="datetime">预测时间</th>
                    <th class="datetime">抵达时间</th>
                    <th>耗时</th>
                    <th>状态</th>
                    <th>订单状态</th>
                    <th>处理状态</th>
                    <th class="username">处理人</th>
                    <th class="datetime">处理时间</th>
                    <th class="last">备注</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($model->routes as $i => $route): ?>
                    <tr>
                        <td class="serial-number"><?= $i + 1 ?></td>
                        <td style="width: 160px;"><?= $route['lineRoute']['event'] ?></td>
                        <td class="date"><?= $formatter->asDate($route['begin_datetime']) ?></td>
                        <td class="number">
                            <?php
                            $lineRoute = $route['lineRoute'];
                            if ($lineRoute['estimate_days'] == 0) {
                                $text = '自动估算';
                            } elseif ($lineRoute['estimate_days'] == -1) {
                                $text = '';
                            } else {
                                $text = $lineRoute['estimate_days'] . ' 天';
                            }

                            echo $text;
                            ?>
                        </td>
                        <td style="width: 80px; text-align: right"><?= ($route['plan_datetime_is_changed'] ? '★ ' : '') . $formatter->asDate($route['plan_datetime']) ?></td>
                        <td class="date"><?= $formatter->asDate($route['end_datetime']) ?></td>
                        <td style="width: 120px">
                            <?php
                            $minutes = $route['take_minutes'];
                            $days = floor($minutes / 1440);
                            $minutes -= $days * 1440;
                            $hours = floor($minutes / 60);
                            $minutes -= $hours * 60;
                            $text = '';
                            $days && $text = "$days 天 ";
                            $hours && $text .= "$hours 小时 ";
                            $minutes && $text .= "$minutes 分钟";
                            echo $text;
                            ?>
                        </td>
                        <td style="width: 50px; text-align: center">
                            <?php
                            $text = $formatter->asPackageRouteStatus($route['status']);
                            switch ($route['status']) {
                                case PackageRoute::STATUS_OVERTIME:
                                    $color = 'red';
                                    break;

                                case PackageRoute::STATUS_IN_ADVANCE:
                                    $color = 'green';
                                    break;

                                default:
                                    $color = 'black';
                                    break;
                            }
                            echo Html::tag('span', $text, ['style' => "color: $color"]);
                            ?>
                        </td>
                        <td style="width: 60px">
                            <?php
                            $lineRoute = $route['lineRoute'];
                            if ($lineRoute['package_status']) {
                                $text = $formatter->asPackageStatus($lineRoute['package_status']);
                            } else {
                                $text = '';
                            }

                            echo $text;
                            ?>
                        </td>
                        <td style="width: 80px; text-align: center"><?= $formatter->asPackageRouteProcessStatus($route['process_status']) ?></td>
                        <td class="username"><?= $route['processMember']['username'] ?></td>
                        <td class="datetime"><?= $formatter->asDatetime($route['process_datetime']) ?></td>
                        <td class="last"><?= $route['remark'] ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>


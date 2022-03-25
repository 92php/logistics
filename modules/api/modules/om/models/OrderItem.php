<?php

namespace app\modules\api\modules\om\models;

use app\modules\api\models\Constant;
use Yii;

/**
 * 订单详情模型
 *
 * @package app\modules\api\modules\om\models
 */
class OrderItem extends \app\modules\api\modules\g\models\OrderItem
{

    public function extraFields()
    {
        return array_merge(parent::extraFields(), [
            'route', 'workflow', 'log'
        ]);
    }

    /**
     * 路由
     *
     * @return \yii\db\ActiveQuery
     */
    public function getRoute()
    {
        return $this->hasMany(OrderItemRoute::class, ['order_item_id' => 'id'])->orderBy(['id' => SORT_DESC]);
    }

    /**
     * 操作日志
     */
    public function getLog()
    {
        // 查询2个表，获取一条路由所有操作
        $items = [];
        $db = Yii::$app->getDb();
        $routes = $db->createCommand("SELECT * FROM {{%om_order_item_route}} WHERE [[order_item_id]] = :orderItemId", [':orderItemId' => $this->id])->queryAll();
        foreach ($routes as $route) {
            $route['is_reissue'] ? $items[$route['id']]['is_reissue'] = true : $items[$route['id']]['is_reissue'] = false;
            // 下单
            $placeOrderBy = $db->createCommand("SELECT [[username]] FROM {{%member}} WHERE [[id]] = :id", [':id' => $route['place_order_by']])->queryScalar();

            $items[$route['id']]['logs'][$route['place_order_at']] = [
                'type' => 'log',
                'message' => date("Y-m-d H:i:s", $route['place_order_at']) . " {$placeOrderBy}下单"
            ];

            // 查询是否有取消记录
            $cancels = $db->createCommand("SELECT * FROM {{%om_order_item_route_cancel_log}} WHERE [[order_item_route_id]] = :orderItemRouteId", [':orderItemRouteId' => $route['id']])->queryAll();
            if ($cancels) {
                // 如果有取消记录
                foreach ($cancels as $cancel) {
                    $type = $cancel['type'] ? '商品' : '供应商';
                    $userName = $db->createCommand("SELECT [[username]] FROM {{%member}} WHERE [[id]] = :id", [':id' => $cancel['canceled_by']])->queryScalar();
                    $items[$route['id']]['logs'][$cancel['canceled_at']] = [
                        'type' => 'cancel',
                        'message' => date("Y-m-d H:i:s", $cancel['canceled_at']) . " {$userName}申请取消" . $type . ', 原因：' . $cancel['canceled_reason']
                    ];

                    if ($cancel['confirmed_status'] == OrderItemRouteCancelLog::STATUS_APPROVE) {
                        // 同意取消
                        $confirmedBy = $db->createCommand("SELECT [[username]] FROM {{%member}} WHERE [[id]] = :id", [':id' => $cancel['confirmed_by']])->queryScalar();
                        $items[$route['id']]['logs'][$cancel['confirmed_at']] = [
                            'type' => 'cancel',
                            'message' => date("Y-m-d H:i:s", $cancel['confirmed_at']) . " 供应商{$confirmedBy}确认取消" . $type
                        ];
                    } elseif ($cancel['confirmed_status'] == OrderItemRouteCancelLog::STATUS_REJECT) {
                        // 拒接取消
                        $confirmedBy = $db->createCommand("SELECT [[username]] FROM {{%member}} WHERE [[id]] = :id", [':id' => $cancel['canceled_by']])->queryScalar();
                        $items[$route['id']]['logs'][$cancel['confirmed_at']] = [
                            'type' => 'cancel',
                            'message' => date("Y-m-d H:i:s", $cancel['confirmed_at']) . " 供应商{$confirmedBy}拒绝取消{$type}, 原因：" . $cancel['confirmed_message']
                        ];
                    }
                }
            }
            $vendor = $db->createCommand("SELECT [[name]] from {{%g_vendor}} WHERE [[id]] = :id", [':id' => $route['vendor_id']])->queryScalar();
            // 是否接单
            if ($route['receipt_status'] == OrderItemRoute::STATUS_PLACE_ORDER_ALREADY) {
                $items[$route['id']]['logs'][$route['receipt_at']] = [
                    'type' => 'log',
                    'message' => date("Y-m-d H:i:s", $route['receipt_at']) . " 供应商{$vendor}接单"
                ];
                // 是否生产
                if ($route['production_status']) {
                    // 生产中
                    $items[$route['id']]['logs'][$route['production_at']] = [
                        'type' => 'log',
                        'message' => date("Y-m-d H:i:s", $route['production_at']) . " 供应商{$vendor}开始生产"
                    ];
                }
                // 是否发货
                if ($route['delivery_status']) {
                    // 发货
                    $items[$route['id']]['logs'][$route['vendor_deliver_at']] = [
                        'type' => 'log',
                        'message' => date("Y-m-d H:i:s", $route['vendor_deliver_at']) . " 供应商{$vendor}发货"
                    ];
                }
                // 是否收货
                if ($route['receiving_status']) {
                    // 收货
                    $items[$route['id']]['logs'][$route['receiving_at']] = [
                        'type' => 'log',
                        'message' => date("Y-m-d H:i:s", $route['receiving_at']) . " 仓库收货"
                    ];
                }
                // 是否质检
                if ($route['inspection_status']) {
                    // 质检
                    $inspectionBy = $db->createCommand("SELECT [[username]] FROM {{%member}} WHERE [[id]] = :id", [':id' => $route['inspection_by']])->queryScalar();

                    $items[$route['id']]['logs'][$route['inspection_at']] = [
                        'type' => 'log',
                        'message' => date("Y-m-d H:i:s", $route['inspection_at']) . " 仓库质检，质检人：{$inspectionBy}"
                    ];
                }
                // 是否入库
                if ($route['warehousing_at']) {
                    // 入库
                    $items[$route['id']]['logs'][$route['warehousing_at']] = [
                        'type' => 'log',
                        'message' => date("Y-m-d H:i:s", $route['warehousing_at']) . " 商品入库，入库数量：" . $route['inspection_number']
                    ];
                }
            } else if ($route['receipt_status'] == OrderItemRoute::STATUS_PLACE_ORDER_REFUSE) {
                // 拒接单
                $items[$route['id']]['logs'][$route['receipt_at']] = [
                    'type' => 'log',
                    'message' => date("Y-m-d H:i:s", $route['receipt_at']) . " 供应商{$vendor}拒接单，原因:" . $route['reason']
                ];
            }
            krsort($items[$route['id']]['logs']);
            $items[$route['id']]['logs'] = array_values($items[$route['id']]['logs']);
        }

        return array_values($items);
    }

}

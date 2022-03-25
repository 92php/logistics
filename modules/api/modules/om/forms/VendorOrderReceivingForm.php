<?php

namespace app\modules\api\modules\om\forms;

use app\jobs\OmDxmOrderStatusJob;
use app\modules\api\modules\om\models\OrderItemRoute;
use app\modules\api\models\Constant;
use app\modules\api\modules\om\models\OrderItemBusiness;
use Yii;
use yii\base\Model;

/**
 *
 * 供货商接单或拒接单form
 *
 * @package app\modules\api\modules\om\forms
 */
class VendorOrderReceivingForm extends Model
{

    /**
     * 商品
     *
     * @var array
     */
    public $products = [];

    public function rules()
    {
        return [
            ['products', 'required'],
            ['products', function ($attribute, $params) {
                $errorMessage = null;
                $products = $this->products;
                if (is_array($products)) {
                    foreach ($products as $product) {
                        if (!array_key_exists('route_id', $product)) {
                            $errorMessage = '必须带有订单路由详情id。';
                            break;
                        }
                        if (!array_key_exists('is_order_receiving', $product)) {
                            // 是否接单
                            $errorMessage = '必须带有是否接单状态。';
                            break;
                        } else {
                            if (!$product['is_order_receiving']) {
                                if (!isset($product['reason'])) {
                                    $errorMessage = '必须带有原因。';
                                    break;
                                } else {
                                    if (empty($product['reason'])) {
                                        $errorMessage = '拒接单必须填写拒接理由。';
                                        break;
                                    }
                                }
                            }
                        }
                        // 如果找不到该条数据
                        $orderRouteModel = OrderItemRoute::findOne(['id' => $product['route_id']]);
                        if ($orderRouteModel == null) {
                            $errorMessage = '未找到该条数据。';
                            break;
                        } else {
                            if ($orderRouteModel->receipt_status != OrderItemRoute::STATUS_PLACE_ORDER_STAY) {
                                $errorMessage = '该数据不是待接单状态，请核实。';
                                break;
                            }
                        }
                    }
                } else {
                    $errorMessage = '数据类型错误';
                }
                if ($errorMessage) {
                    $this->addError($attribute, $errorMessage);
                }
            }]
        ];
    }

    /**
     * 保存数据
     *
     * @throws \Exception
     * @throws \Throwable
     */
    public function save()
    {
        $db = Yii::$app->getDb();
        $transaction = $db->beginTransaction();
        $cmd = $db->createCommand();
        $isSuccess = false;
        try {
            foreach ($this->products as $product) {
                $orderRouteModel = OrderItemRoute::findOne(['id' => $product['route_id']]);
                if ($product['is_order_receiving']) {
                    // 如果以接单状态
                    $orderRouteModel->receipt_status = OrderItemRoute::STATUS_PLACE_ORDER_ALREADY;
                    $orderRouteModel->receipt_at = time();
                    $orderRouteModel->current_node = OrderItemRoute::NODE_TO_PRODUCED;
                    //　判断该订单下订单是否全部被接单，如果是则加入队列
                    $status = $db->createCommand("SELECT [[b.status]] FROM {{%g_order_item}} oi INNER JOIN {{%om_order_item_business}} b ON b.order_item_id = oi.id WHERE [[oi.order_id]] = :orderId AND [[oi.ignored]] = :ignored", [':orderId' => $orderRouteModel->item->order_id, ':ignored' => Constant::BOOLEAN_FALSE])->queryColumn();

                    if (array_sum($status) == count($status) * OrderItemBusiness::STATUS_IN_HANDLE) {
                        $packageId = $db->createCommand("SELECT [[p.id]] FROM {{%g_package_order_item}} poi INNER JOIN {{%g_package}} p ON [[p.id]] = [[poi.package_id]] WHERE [[poi.order_item_id]] = :orderItemId", [':orderItemId' => $orderRouteModel->order_item_id])->queryScalar();
                        if ($packageId) {
                            Yii::$app->queue->push(new OmDxmOrderStatusJob([
                                'id' => $packageId,
                                'type' => 1
                            ]));
                        }
                    }
                } else {
                    // 拒接
                    $orderRouteModel->receipt_status = OrderItemRoute::STATUS_PLACE_ORDER_REFUSE;
                    $orderRouteModel->reason = $product['reason'];
                    $orderRouteModel->receipt_at = time();
                    $orderRouteModel->current_node = OrderItemRoute::NODE_REJECT_ORDER;
                    // 拒接单以后清空订单详情中供应商， 状态改为拒接
                    $isSuccess = $cmd->update("{{%om_order_item_business}}", ['status' => OrderItemBusiness::STATUS_STAY_REJECT], ['order_item_id' => $orderRouteModel['order_item_id']])->execute();
                    if (!$isSuccess) {
                        break;
                    }
                    $isSuccess = $cmd->update("{{%g_order_item}}", ['vendor_id' => 0, 'cost_price' => 0], ['id' => $orderRouteModel['order_item_id']])->execute();
                    if (!$isSuccess) {
                        break;
                    }
                }
                $isSuccess = $orderRouteModel->save();
                if (!$isSuccess) {
                    break;
                }
            }
            if ($isSuccess) {
                $transaction->commit();
            } else {
                $transaction->rollBack();
            }

            return true;
        } catch (\Exception $e) {
            $transaction->rollBack();
            throw $e;
        } catch (\Throwable $e) {
            $transaction->rollBack();
            throw $e;
        }
    }
}

<?php

namespace app\modules\api\modules\om\forms;

use app\modules\api\modules\om\models\OrderItem;
use app\modules\api\modules\om\models\OrderItemBusiness;
use app\modules\api\modules\om\models\OrderItemRoute;
use Yii;
use yii\base\Model;

/**
 *  商品下单和批量下单
 *
 * @package app\modules\api\modules\om\forms
 */
class PlaceOrderBatchForm extends Model
{

    /**
     * @var array 商品数据
     */
    public $products = [];

    public function rules()
    {
        return [
            [['products'], 'required'],
            ['products', function ($attribute, $params) {
                $errorMessage = null;
                $products = $this->products;
                if (is_array($products)) {
                    foreach ($products as $product) {
                        $model = OrderItem::findOne(['id' => $product['id']]);
                        if (!in_array($model->business->status, [OrderItemBusiness::STATUS_STAY_PLACE_ORDER, OrderItemBusiness::STATUS_IN_HANDLE, OrderItemBusiness::STATUS_STAY_REJECT])) {
                            $errorMessage = "商品下单必须是待下单、处理中、拒接单状态订单。";
                            break;
                        }
                        if ($model->vendor_id) {
                            $errorMessage = "已分配商品不可再次分配。";
                            break;
                        }
                        if ($model->ignored) {
                            $errorMessage = "无效商品不可下单。";
                            break;
                        }
                        if (!array_key_exists('id', $product)) {
                            $errorMessage = "商品下单数据必须带有id。";
                            break;
                        }
                        if (!array_key_exists('vendor_id', $product)) {
                            $errorMessage = "商品下单数据必须带有供应商。";
                            break;
                        }
                        if (!array_key_exists('cost_price', $product)) {
                            $errorMessage = "商品下单数据必须带有成本价。";
                            break;
                        }
                        $payload = [
                            'vendor_id' => $product['vendor_id']
                        ];
                        $model->load($payload, '');
                        if (!$model->validate()) {
                            if ($model->hasErrors()) {
                                foreach ($model->getErrors() as $error) {
                                    $errorMessage = $error[0];
                                    break;
                                }
                            } else {
                                $errorMessage = '未知错误。';
                            }
                            break;
                        }
                        $OrderRouteModel = new OrderItemRoute();
                        $routePayload = [
                            'order_item_id' => $product['id'],
                            'vendor_id' => $product['vendor_id'],
                            'quantity' => $model['quantity'],
                            'receipt_status' => OrderItemRoute::STATUS_PLACE_ORDER_STAY,
                            'cost_price' => $product['cost_price'],
                            'current_node' => OrderItemRoute::NODE_STAY_RECEIPT
                        ];
                        $OrderRouteModel->load($routePayload, '');
                        if (!$OrderRouteModel->validate()) {
                            if ($OrderRouteModel->hasErrors()) {
                                foreach ($OrderRouteModel->getErrors() as $error) {
                                    $errorMessage = $error[0];
                                    break;
                                }
                            } else {
                                $errorMessage = '未知错误。';
                            }
                            break;
                        }
                    }
                } else {
                    $errorMessage = '错误的商品下单数据格式。';
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
        $transaction = Yii::$app->getDb()->beginTransaction();
        try {
            $isSuccess = false;
            foreach ($this->products as $product) {
                // 下单给供应商
                $model = OrderItem::findOne(['id' => $product['id']]);
                $model->loadDefaultValues();
                $model->cost_price = ($product['cost_price'] * $model['quantity']);
                $model->vendor_id = $product['vendor_id'];
                $isSuccess = $model->save();

                // 修改状态
                $orderItemModel = OrderItemBusiness::find()->where(['order_item_id' => $product['id']])->one();
                $orderItemModel->status = OrderItemBusiness::STATUS_IN_HANDLE;
                $isSuccess = $orderItemModel->save();
                // 生成一条路由记录
                $orderRouteModel = new OrderItemRoute();
                $orderRouteModel->loadDefaultValues();
                $routePayload = [
                    'order_item_id' => $product['id'],
                    'vendor_id' => $product['vendor_id'],
                    'quantity' => $model['quantity'],
                    'receipt_status' => OrderItemRoute::STATUS_PLACE_ORDER_STAY,
                    'cost_price' => $product['cost_price'],
                    'current_node' => OrderItemRoute::NODE_STAY_RECEIPT
                ];
                $orderRouteModel->load($routePayload, '');
                $isSuccess = $orderRouteModel->save();
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
<?php

namespace app\modules\api\modules\om\forms;

use app\jobs\OmDxmOrderStatusJob;
use app\modules\api\models\Constant;
use app\modules\api\modules\om\models\OrderItemBusiness;
use app\modules\api\modules\om\models\OrderItemRoute;
use Yii;
use yii\base\Model;
use yii\db\Query;

/**
 * Class BatchWarehousingForm
 * 商品批量入库
 *
 * @package app\modules\api\modules\om\forms
 */
class BatchWarehousingForm extends Model
{

    /**
     * 商品id
     *
     * @var array
     */
    public $order_item_ids = [];

    /**
     * 是否通过
     *
     * @var boolean
     */
    public $is_pass;

    /**
     * 信息反馈(是否通过为false时必传)
     *
     * @var string
     */
    public $information_feedback;

    /**
     * 反馈(是否通过为false时必传)
     *
     * @var string
     */
    public $feedback;

    public function rules()
    {
        return [
            ['order_item_ids', 'required'],
            [['information_feedback'], 'required', 'when' => function ($model) {
                return $model->is_pass == Constant::BOOLEAN_FALSE;
            }],
            ['is_pass', 'boolean'],
            ['order_item_ids', 'safe'],
            [['information_feedback', 'feedback'], 'string'],
            ['order_item_ids', function ($attribute, $params) {
                $products = (new Query())->select(['oi.id', 'r.current_node', 'b.status'])->from("{{%g_order_item}} oi")->innerJoin(" {{%om_order_item_route}} r", 'r.order_item_id = oi.id')->innerJoin("{{%om_order_item_business}} b", 'b.order_item_id = oi.id')->where(['IN', 'oi.id', $this->order_item_ids])->orderBy('r.id desc')->all();
                if ($products) {
                    $diffOrderItemId = [];
                    foreach ($products as $product) {
                        $diffOrderItemId[] = $product['id'];
                        if ($product['status'] != OrderItemBusiness::STATUS_IN_HANDLE) {
                            $this->addError($attribute, '订单必须是在处理中。');
                        } else {
                            if (!in_array($product['current_node'], [OrderItemRoute::NODE_ALREADY_SHIPPED, OrderItemRoute::NODE_STAY_INSPECTION])) {
                                $this->addError($attribute, '当前节点必须是待收货或者待质检状态。');
                            }
                        }
                    }
                    $result = array_diff($this->order_item_ids, $diffOrderItemId);
                    if ($result) {
                        $this->addError($attribute, '有商品未找到，请检查。');
                    }
                }
            }],
        ];
    }

    /**
     * @return bool
     * @throws \Throwable
     */
    public function save()
    {
        $db = Yii::$app->getDb();
        $cmd = $db->createCommand();
        $transaction = $db->beginTransaction();
        $isSuccess = false;
        try {
            foreach ($this->order_item_ids as $order_item_id) {
                $routeModel = OrderItemRoute::find()->where(['order_item_id' => $order_item_id])->orderBy(['id' => SORT_DESC])->one();
                $now = time();
                //　质检完成
                $payload = [
                    'is_accord_with' => $this->is_pass,
                    'is_information_match' => $this->is_pass,
                    'inspection_number' => $routeModel->quantity,
                    'inspection_status' => OrderItemRoute::STATUS_INSPECTION_SUCCESS,
                    'current_node' => OrderItemRoute::NODE_ALREADY_COMPLETE,
                    'inspection_at' => $now,
                    'inspection_by' => Yii::$app->getUser()->getId(),
                    'warehousing_at' => $now
                ];
                if (!empty($this->feedback)) {
                    $payload['feedback'] = $this->feedback;
                }
                if (!empty($this->information_feedback)) {
                    $payload['information_feedback'] = $this->information_feedback;
                }
                // 如果isPass为false 则重新生成路由
                if (!$this->is_pass) {
                    $model = new OrderItemRoute();
                    $routePayload = [
                        'parent_id' => $routeModel->id,
                        'order_item_id' => $routeModel->order_item_id,
                        'vendor_id' => $routeModel->vendor_id,
                        'quantity' => $routeModel->quantity,
                        'receipt_status' => OrderItemRoute::STATUS_PLACE_ORDER_STAY,
                        'cost_price' => $routeModel->cost_price,
                        'is_reissue' => Constant::BOOLEAN_TRUE,
                        'current_node' => OrderItemRoute::NODE_STAY_RECEIPT
                    ];
                    $model->load($routePayload, '');
                    $isSuccess = $model->save();
                    if (!$isSuccess) {
                        break;
                    }
                } else {
                    // 如果质检数量不少于 则代表单已经做完，
                    $isSuccess = $cmd->update("{{%om_order_item_business}}", ['status' => OrderItemBusiness::STATUS_STAY_COMPLETE], ['order_item_id' => $order_item_id])->execute();
                    if (!$isSuccess) {
                        break;
                    }
                    //　判断该订单下订单是否全部被接单，如果是则加入队列
                    $status = $db->createCommand("SELECT [[b.status]] FROM {{%g_order_item}} oi LEFT JOIN {{%om_order_item_business}} b ON b.order_item_id = oi.id WHERE [[oi.order_id]] = :orderId AND [[oi.ignored]] = :ignored", [':orderId' => $routeModel->item->order_id, ':ignored' => Constant::BOOLEAN_FALSE])->queryColumn();
                    if (array_sum($status) == count($status) * OrderItemBusiness::STATUS_STAY_COMPLETE) {
                        Yii::$app->queue->push(new OmDxmOrderStatusJob([
                            'id' => $routeModel->item->order_id,
                            'type' => 2
                        ]));
                    }
                }

                $routeModel->load($payload, '');
                $isSuccess = $routeModel->save();
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
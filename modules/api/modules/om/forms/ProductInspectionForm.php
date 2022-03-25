<?php

namespace app\modules\api\modules\om\forms;

use app\jobs\OmDxmOrderStatusJob;
use app\modules\api\models\Constant;
use app\modules\api\modules\om\models\OrderItemBusiness;
use app\modules\api\modules\om\models\OrderItemRoute;
use Yii;
use yii\base\Model;

/**
 * Class ProductInspectionForm
 * 产品质检form
 *
 * @package app\modules\api\modules\om\forms
 */
class ProductInspectionForm extends Model
{

    /**
     * 商品id
     *
     * @var integer
     */
    public $order_item_id;

    /**
     * 反馈
     *
     * @var string
     */
    public $feedback;

    /**
     * 信息反馈
     *
     * @var string
     */
    public $information_feedback;

    /**
     * 是否符合质检标准
     *
     * @var integer
     */
    public $is_accord_with;

    /**
     * 反馈图片
     *
     * @var integer
     */
    public $feedback_image;

    /**
     * 信息反馈图片
     *
     * @var integer
     */
    public $information_image;

    /**
     * 是否信息匹配
     *
     * @var integer
     */
    public $is_information_match;

    /**
     * 数量
     *
     * @var int
     */
    public $quantity;

    public function rules()
    {
        return [
            [['order_item_id', 'is_accord_with', 'is_information_match', 'quantity'], 'required'],
            [['is_accord_with', 'is_information_match', 'order_item_id', 'quantity'], 'integer'],
            [['feedback', 'information_feedback', 'feedback_image', 'information_image'], 'string'],
            ['is_accord_with', function ($attribute, $params) {
                if (!$this->is_accord_with && empty($this->feedback)) {
                    $this->addError($attribute, '不符合质量标准必须填写反馈');
                }
            }],
            ['is_information_match', function ($attribute, $params) {
                if (!$this->is_information_match && empty($this->information_feedback)) {
                    $this->addError($attribute, '不符合信息匹配必须填写原因');
                }
            }],
            ['order_item_id', function ($attribute, $params) {
                // 查询数据状态，获取商品状态。路由状态。数量等
                $product = Yii::$app->getDb()->createCommand("SELECT [[r.id]],[[r.quantity]], [[r.current_node]], [[b.status]] FROM {{%g_order_item}} oi INNER JOIN {{%om_order_item_route}} r on [[r.order_item_id]] = [[oi.id]] INNER JOIN {{%om_order_item_business}} b on [[b.order_item_id]] = [[oi.id]] WHERE [[oi.id]] = :id ORDER BY r.id desc ", [':id' => $this->order_item_id])->queryOne();
                if (!$product) {
                    $this->addError($attribute, '未找到该商品');
                } else {
                    if ($product['status'] != OrderItemBusiness::STATUS_IN_HANDLE) {
                        $this->addError($attribute, '订单必须是在处理中。');
                    } else {
                        if (!in_array($product['current_node'], [OrderItemRoute::NODE_ALREADY_SHIPPED, OrderItemRoute::NODE_STAY_INSPECTION])) {
                            $this->addError($attribute, '当前节点必须是待收货或者待质检状态。');
                        }

                        if ($this->quantity > $product['quantity']) {
                            $this->addError($attribute, '质检数量不可大于商品数量。');
                        }
                    }
                }
                // 判断信息匹配也符合质量标准 必须传入数量大于0
                if ($this->is_information_match && $this->is_accord_with && $this->quantity < 1) {
                    $this->addError($attribute, '质检通过必须传入商品数量。');
                }
            }],
            ['information_image', function ($attribute, $params) {
                $webRoot = Yii::getAlias('@webroot');
                if (!file_exists($webRoot . $this->information_image)) {
                    $this->addError($attribute, $this->information_image . " 文件不存在。");
                }
            }],
            ['feedback_image', function ($attribute, $params) {
                $webRoot = Yii::getAlias('@webroot');
                if (!file_exists($webRoot . $this->feedback_image)) {
                    $this->addError($attribute, $this->feedback_image . " 文件不存在。");
                }
            }]
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
            $routeModel = OrderItemRoute::find()->where(['order_item_id' => $this->order_item_id])->orderBy(['id' => SORT_DESC])->one();
            //　质检完成
            $payload = [
                'is_accord_with' => $this->is_accord_with,
                'is_information_match' => $this->is_information_match,
                'inspection_number' => $this->quantity,
                'inspection_status' => OrderItemRoute::STATUS_INSPECTION_SUCCESS,
                'current_node' => OrderItemRoute::NODE_ALREADY_COMPLETE,
                'inspection_at' => time(),
                'inspection_by' => Yii::$app->getUser()->getId(),
                'feedback_image' => !empty($this->feedback_image) ? $this->feedback_image : "",
                'information_image' => !empty($this->information_image) ? $this->information_image : ""
            ];
            if (!empty($this->feedback)) {
                $payload['feedback'] = $this->feedback;
            }
            if (!empty($this->information_feedback)) {
                $payload['information_feedback'] = $this->information_feedback;
            }
            // 如果质检数量少于商品数量。则重新生成一条路由 补单状态
            if ($this->quantity < $routeModel->quantity) {
                $model = new OrderItemRoute();
                $routePayload = [
                    'parent_id' => $routeModel->id,
                    'order_item_id' => $routeModel->order_item_id,
                    'vendor_id' => $routeModel->vendor_id,
                    'quantity' => ($routeModel->quantity - $this->quantity),
                    'receipt_status' => OrderItemRoute::STATUS_PLACE_ORDER_STAY,
                    'cost_price' => $routeModel->cost_price,
                    'is_reissue' => Constant::BOOLEAN_TRUE,
                    'current_node' => OrderItemRoute::NODE_STAY_RECEIPT
                ];
                $model->load($routePayload, '');
                $isSuccess = $model->save();
                if (!$isSuccess) {
                    $transaction->rollBack();
                }
            } else {
                // 如果质检数量不少于 则代表单已经做完，
                $isSuccess = $cmd->update("{{%om_order_item_business}}", ['status' => OrderItemBusiness::STATUS_STAY_COMPLETE], ['order_item_id' => $routeModel->order_item_id])->execute();
                if (!$isSuccess) {
                    $transaction->rollBack();
                }
                //　判断该订单下订单是否全部被接单，如果是则加入队列
                $status = $db->createCommand("SELECT [[b.status]] FROM {{%g_order_item}} oi LEFT JOIN {{%om_order_item_business}} b ON b.order_item_id = oi.id WHERE [[oi.order_id]] = :orderId AND [[oi.ignored]] = :ignored", [':orderId' => $routeModel->item->order_id, ':ignored' => Constant::BOOLEAN_FALSE])->queryColumn();

                if (array_sum($status) == count($status) * OrderItemBusiness::STATUS_STAY_COMPLETE) {
                    $packageId = $db->createCommand("SELECT [[p.id]] FROM {{%g_package_order_item}} poi INNER JOIN {{%g_package}} p ON [[p.id]] = [[poi.package_id]] WHERE [[poi.order_item_id]] = :orderItemId", [':orderItemId' => $routeModel->order_item_id])->queryScalar();
                    Yii::$app->queue->push(new OmDxmOrderStatusJob([
                        'id' => $packageId,
                        'type' => 2
                    ]));
                }
            }
            if ($this->quantity) {
                //质检数量不为0 则添加入库时间
                $payload['warehousing_at'] = time();
            }
            $routeModel->load($payload, '');
            $isSuccess = $routeModel->save();
            if (!$isSuccess) {
                $transaction->rollBack();
            } else {
                $transaction->commit();
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
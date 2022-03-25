<?php

namespace app\modules\api\modules\om\forms;

use app\modules\api\modules\om\models\OrderItem;
use app\modules\api\modules\om\models\OrderItemBusiness;
use Yii;
use yii\base\Model;

/**
 * Class ProductCheckForm
 * 核对订单
 *
 * @package app\modules\api\modules\om\forms
 */
class ProductCheckForm extends Model
{

    /**
     * 订单详情id
     *
     * @var integer
     */
    public $order_item_id;

    /**
     * 定制信息
     *
     * @var string
     */
    public $customized;

    /**
     * 备注
     *
     * @var string
     */
    public $remark;

    /**
     * @var int 优先级
     */
    public $priority;

    public function rules()
    {
        return [
            ['order_item_id', 'required'],
            ['customized', 'safe'],
            ['remark', 'string'],
            [['customized', 'remark'], 'trim'],
            ['order_item_id', 'integer'],
            ['order_item_id', function ($attribute, $params) {
                $orderItem = OrderItem::findOne(['id' => $this->order_item_id]);
                if ($orderItem) {
                    if ($orderItem->ignored) {
                        $this->addError($attribute, "无效商品不可核单。");
                    }
                    if ($orderItem->business->status != OrderItemBusiness::STATUS_STAY_CHECK) {
                        $this->addError($attribute, "商品必须为待核实商品。");
                    }
                } else {
                    $this->addError($attribute, "未找到该商品。");
                }
            }],
            ['customized', function ($attribute, $params) {
                if (!is_array($this->customized)) {
                    $this->addError($attribute, "格式不正确。");
                }
            }],
            ['priority', 'integer'],
            ['priority', 'default', 'value' => OrderItemBusiness::PRIORITY_NORMAL],
            ['priority', 'in', 'range' => array_keys(OrderItemBusiness::priorityOptions())],
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
        $transaction = Yii::$app->db->beginTransaction();
        try {
            $model = OrderItem::findOne(['id' => $this->order_item_id]);
            $model->loadDefaultValues();
            if ($this->remark) {
                $model->remark = $this->remark;
            }
            // 如果有定制信息
            if ($this->customized) {
                // 定制信息格式需判断
                $extend = $model->extend;
                foreach ($this->customized as $key => $item) {
                    if ($key != 'raw' && isset($extend[$key])) {
                        $extend[$key] = $item;
                    }
                }
                $model->extend = $extend;
            }
            $isSuccess = $model->save();
            $orderItemModel = OrderItemBusiness::find()->where(['order_item_id' => $this->order_item_id])->one();
            /* @var $orderItemModel OrderItemBusiness */
            $orderItemModel->status = OrderItemBusiness::STATUS_STAY_PLACE_ORDER;
            $orderItemModel->priority = $this->priority;
            $isSuccess = $orderItemModel->save();
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

    public function attributeLabels()
    {
        return [
            'priority' => '优先级',
            'remark' => '备注',
        ];
    }

}

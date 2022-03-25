<?php

namespace app\business;

use app\models\Constant;
use app\modules\admin\modules\g\models\OrderItem;
use app\modules\admin\modules\om\models\OrderItemBusiness;
use Yii;

/**
 * 物流订单项目业务处理
 *
 * @package app\business
 */
class OmModuleOrderItemBusiness implements OrderItemBusinessInterface
{

    /**
     * @param OrderItem $orderItem
     * @param $insert
     * @param array $changedAttributes
     * @param array $params
     * @throws \yii\db\Exception
     */
    public function afterSave(OrderItem $orderItem, bool $insert, array $changedAttributes, array $params)
    {
        if ($insert) {
            // 订单创建后，同步在 om_order_item_business 中创建数据
            $model = new OrderItemBusiness();
            $model->order_item_id = $orderItem->id;

            $order = Yii::$app->getDb()->createCommand('SELECT [[third_party_platform_id]], [[third_party_platform_status]] FROM {{%g_order}} WHERE [[id]] = :id', [':id' => $orderItem->order_id])->queryOne();
            $status = null;
            if ($order) {
                // 已经完成的订单项目需要同步更新为已完成状态
                switch ($order['third_party_platform_id']) {
                    case Constant::THIRD_PARTY_PLATFORM_DIAN_XIAO_MI:
                        if ($order['third_party_platform_status'] == Constant::THIRD_PARTY_PLATFORM_DXM_ORDER_STATUS_YI_WAN_CHENG) {
                            $status = OrderItemBusiness::STATUS_STAY_COMPLETE;
                        }
                        break;

                    case Constant::THIRD_PARTY_PLATFORM_TONG_TOOL:
                        break;
                }
            }
            if ($status !== null) {
                $model->status = $status;
            }
            $model->save();
        }
    }

    /**
     * @param OrderItem $orderItem
     * @param array $params
     * @throws \yii\db\Exception
     */
    public function afterDelete(OrderItem $orderItem, array $params)
    {
        Yii::$app->getDb()->createCommand()->delete(OrderItemBusiness::tableName(), ['order_item_id' => $orderItem->id])->execute();
    }

}
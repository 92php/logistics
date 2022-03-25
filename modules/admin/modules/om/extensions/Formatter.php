<?php

namespace app\modules\admin\modules\om\extensions;

use app\modules\admin\modules\om\models\OrderItemRoute;
use app\modules\admin\modules\om\models\OrderItemRouteCancelLog;
use app\modules\api\modules\om\models\OrderItemBusiness;

/**
 * Class Formatter
 *
 * @package app\modules\admin\modules\om\extensions

 */
class Formatter extends \app\modules\admin\modules\g\extensions\Formatter
{

    /**
     * 商品路由当前节点
     *
     * @param $value
     * @return mixed|string|null
     */
    public function asOrderItemRouteStatus($value)
    {
        $options = OrderItemRoute::statusOptions();

        return isset($options[$value]) ? $options[$value] : null;
    }

    /**
     * 商品路由接单状态
     *
     * @param $value
     * @return mixed|string|null
     */
    public function asOrderItemRoutePlaceOrderStatus($value)
    {
        $options = OrderItemRoute::PlaceOrderStatusOption();

        return isset($options[$value]) ? $options[$value] : null;
    }

    /**
     * 取消类型
     *
     * @param $value
     * @return mixed|string|null
     */
    public function asCancelType($value)
    {
        $options = OrderItemRouteCancelLog::CancelTypeOption();

        return isset($options[$value]) ? $options[$value] : null;
    }

    /**
     * 取消申请的确认状态
     *
     * @param $value
     * @return mixed|string|null
     */
    public function asConfirmStatus($value)
    {
        $options = OrderItemRouteCancelLog::ConfirmStatusOption();

        return isset($options[$value]) ? $options[$value] : null;
    }

    /**
     * 商品状态
     *
     * @param $value
     * @return mixed|string|null
     */
    public function asOrderItemBusinessStatus($value)
    {
        $options = OrderItemBusiness::statusOptions();

        return isset($options[$value]) ? $options[$value] : null;
    }

}

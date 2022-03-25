<?php

namespace app\business;

use app\modules\admin\modules\g\models\OrderItem;

/**
 * 会员业务处理接口类
 *
 * @package app\business
 */
interface OrderItemBusinessInterface
{

    /**
     * 订单商品业务逻辑处理代码
     *
     * 当业务处理遇到问题时，请抛出异常，调用端会截获到您抛出的异常，涉及到数据库的部分，将进行回滚，并将错误信息记录到日志中，方便排查问题。
     *
     * @param OrderItem $orderItem
     * @param boolean $insert
     * @param array $changedAttributes
     * @param array $params
     * @return bool
     */
    public function afterSave(OrderItem $orderItem, bool $insert, array $changedAttributes, array $params);

    public function afterDelete(OrderItem $orderItem, array $params);

}
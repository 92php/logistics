<?php

namespace app\modules\api\modules\om\models;

/**
 * Class OrderBusiness
 * 订单业务接口
 *
 * @package app\modules\api\modules\om\models
 */
class OrderBusiness extends Order
{

    const STATUS_WAIT_HANDLE = 0; // 待处理
    const STATUS_IN_HANDLE = 1; // 处理中
    const STATUS_ALREADY_COMPLETED = 2; // 已完成
    const STATUS_ALREADY_CANCEL = 3; // 已取消

    /**
     * 状态
     *
     * @return array
     */
    public function orderStatusOption()
    {
        return [
            self::STATUS_WAIT_HANDLE => '待处理',
            self::STATUS_IN_HANDLE => '处理中',
            self::STATUS_ALREADY_COMPLETED => '已完成',
            self::STATUS_ALREADY_CANCEL => '已取消',
        ];
    }
}
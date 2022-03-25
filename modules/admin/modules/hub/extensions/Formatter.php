<?php

namespace app\modules\admin\modules\hub\extensions;

use app\modules\api\models\Option;
use app\modules\api\modules\hub\models\OrderLineItem;

class Formatter extends \app\modules\admin\extensions\Formatter
{

    /**
     * 所属平台
     *
     * @param $value
     * @return mixed|null
     */
    public function asPlatform($value)
    {
        $options = Option::platforms(true);

        return isset($options[$value]) ? $options[$value] : null;
    }

    /**
     * 订单商品状态
     *
     * @param $value
     * @return mixed|null
     */
    public function asOrderLineItemStatus($value)
    {
        $options = OrderLineItem::statusOptions();

        return isset($options[$value]) ? $options[$value] : null;
    }

}
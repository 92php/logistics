<?php

namespace app\modules\api\modules\om\models;

use app\modules\api\extensions\AppHelper;
use app\modules\api\modules\g\models\Vendor;
use app\modules\api\modules\om\extensions\Formatter;
use Yii;

/**
 * 订单路由模型
 *
 * @package app\modules\api\modules\om\models *
 */
class OrderItemRoute extends \app\modules\admin\modules\om\models\OrderItemRoute
{

    public function fields()
    {
        /* @var $formatter Formatter */
        $formatter = Yii::$app->getFormatter();

        return [
            'id',
            'package_id',
            'parent_id',
            'order_item_id',
            'place_order_at',
            'place_order_by',
            'vendor_id',
            'receipt_status',
            'production_at',
            'production_status',
            'vendor_deliver_at',
            'delivery_status',
            'receiving_at',
            'receiving_status',
            'inspection_at',
            'inspection_status',
            'inspection_by',
            'warehousing_at',
            'inspection_number',
            'is_reissue' => function ($model) {
                return boolval($model->is_reissue);
            },
            'quantity',
            'status',
            'status_formatted' => function ($model) use ($formatter) {
                return $formatter->asOrderItemRouteStatus($model->current_node);
            },
            'reason',
            'feedback',
            'feedback_image' => function ($model) {
                AppHelper::fixStaticAssetUrl($model->feedback_image);
            },
            'information_feedback',
            'information_image' => function ($model) {
                AppHelper::fixStaticAssetUrl($model->information_image);
            },
            'is_accord_with' => function ($model) {
                return boolval($model->is_accord_with);
            },

            'is_information_match' => function ($model) {
                return boolval($model->is_information_match);
            },
            'cost_price',
            'current_node',
            'is_print' => function ($model) {
                return boolval($model->is_print);
            },
            'is_export' => function ($model) {
                return boolval($model->is_export);
            }
        ];
    }

    /**
     * 供应商
     *
     * @return \yii\db\ActiveQuery
     */
    public function getVendor()
    {
        return $this->hasOne(Vendor::class, ['id' => 'vendor_id']);
    }

    /**
     * 商品数据
     *
     * @return \yii\db\ActiveQuery
     */
    public function getItem()
    {
        return $this->hasOne(OrderItem::class, ['id' => 'order_item_id']);
    }

    public function extraFields()
    {
        return ['vendor', 'item', 'package'];
    }
}

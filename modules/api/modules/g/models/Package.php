<?php

namespace app\modules\api\modules\g\models;

use app\modules\admin\modules\g\extensions\Formatter;
use Yii;

class Package extends \app\modules\admin\modules\g\models\Package
{

    public function fields()
    {
        /* @var $formatter Formatter */
        $formatter = Yii::$app->getFormatter();

        return [
            'id',
            'key',
            'number',
            'country_id',
            'waybill_number',
            'weight',
            'weight_datetime',
            'reference_weight',
            'reference_freight_cost',
            'freight_cost',
            'delivery_datetime',
            'delivery_datetime_formatted' => function ($model) {
                return date('Y-m-d H:i:s', $model->delivery_datetime);
            },
            'logistics_line_id',
            'logistics_query_results' => function ($model) {
                $results = json_decode($model->logistics_query_raw_results, true);

                return $results === null ? [] : $results;
            },
            'logistics_last_check_datetime',
            'estimate_days',
            'final_days',
            'sync_status',
            'shop_id',
            'third_party_platform_id',
            'third_party_platform_id_formatted' => function ($model) use ($formatter) {
                return $formatter->asThirdPartyPlatform($model->third_party_platform_id);
            },
            'third_party_platform_status',
            'third_party_platform_status_formatted' => function ($model) use ($formatter) {
                return $formatter->asThirdPlatformPackageStatus($model->third_party_platform_status, $model->third_party_platform_id);
            },
            'status',
            'status_formatted' => function ($model) use ($formatter) {
                return $formatter->asPackageStatus($model->status);
            },
            'remark',
            'created_at',
            'created_by',
            'updated_at',
            'updated_by',
        ];
    }

    public function extraFields()
    {
        return ['country', 'shop', 'orders', 'items'];
    }

    /**
     * 所属国家
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCountry()
    {
        return $this->hasOne(Country::class, ['id' => 'country_id']);
    }

    /**
     * 所属店铺
     *
     * @return \yii\db\ActiveQuery
     */
    public function getShop()
    {
        return $this->hasOne(Shop::class, ['id' => 'shop_id']);
    }

    /**
     * 包裹订单
     *
     * @return \yii\db\ActiveQuery
     * @throws \yii\base\InvalidConfigException
     */
    public function getOrders()
    {
        return $this->hasMany(Order::class, ['id' => 'order_id'])
            ->viaTable(PackageOrderItem::tableName(), ['package_id' => 'id']);
    }

    /**
     * 包裹商品
     *
     * @return \yii\db\ActiveQuery
     * @throws \yii\base\InvalidConfigException
     */
    public function getItems()
    {
        return $this->hasMany(OrderItem::class, ['id' => 'order_item_id'])
            ->viaTable(PackageOrderItem::tableName(), ['package_id' => 'id']);
    }

}
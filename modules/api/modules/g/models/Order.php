<?php

namespace app\modules\api\modules\g\models;

use app\modules\admin\modules\g\extensions\Formatter;
use app\modules\api\models\Constant;
use Yii;

class Order extends \app\modules\admin\modules\g\models\Order
{

    public function fields()
    {
        /* @var $formatter Formatter */
        $formatter = Yii::$app->getFormatter();

        return [
            'id',
            'key',
            'number',
            'type',
            'consignee_name',
            'consignee_mobile_phone',
            'consignee_tel',
            'country_id',
            'consignee_state',
            'consignee_city',
            'consignee_address1',
            'consignee_address2',
            'consignee_postcode',
            'quantity',
            'total_amount',
            'third_party_platform_id',
            'third_party_platform_id_formatted' => function ($model) use ($formatter) {
                return $formatter->asThirdPartyPlatform($model->third_party_platform_id);
            },
            'third_party_platform_status',
            'third_party_platform_status_formatted' => function ($model) use ($formatter) {
                return $formatter->asThirdPlatformOrderStatus($model->third_party_platform_status, $model->third_party_platform_id);
            },
            'status',
            'status_formatted' => function ($model) use ($formatter) {
                return $formatter->asOrderStatus($model->status);
            },
            'platform_id',
            'platform_id_formatted' => function ($model) use ($formatter) {
                return $formatter->asPlatform($model->platform_id);
            },
            'shop_id',
            'shop_name' => function ($model) {
                $shop = $model->shop;

                return $shop ? $shop->name : '';
            },
            'product_type',
            'product_type_formatted' => function ($model) use ($formatter) {
                return $formatter->asProductType($model->product_type);
            },
            'place_order_at',
            'payment_at',
            'cancelled_at',
            'cancel_reason',
            'closed_at',
            'remark',
            'created_at',
            'created_by',
            'updated_at',
            'updated_by',
        ];
    }

    public function extraFields()
    {
        return ['country', 'shop', 'items', 'packages', 'valid-items' => 'validItems'];
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
     * 订单详情列表
     *
     * @return \yii\db\ActiveQuery
     */
    public function getItems()
    {
        return $this->hasMany(get_called_class() . 'Item', ['order_id' => 'id']);
    }

    /**
     * 有效订单详情列表
     *
     * @return \yii\db\ActiveQuery
     */
    public function getValidItems()
    {
        return $this->hasMany(get_called_class() . 'Item', ['order_id' => 'id'])->where(['ignored' => Constant::BOOLEAN_FALSE]);
    }

    /**
     * 包裹列表
     *
     * @return \yii\db\ActiveQuery
     * @throws \yii\base\InvalidConfigException
     */
    public function getPackages()
    {
        return $this->hasMany(Package::class, ['id' => 'package_id'])
            ->viaTable(PackageOrderItem::tableName(), ['order_id' => 'id']);
    }

}

<?php

namespace app\modules\api\modules\g\models;

use app\modules\api\extensions\AppHelper;
use Yii;

class OrderItem extends \app\modules\admin\modules\g\models\OrderItem
{

    public function fields()
    {
        return [
            'id',
            'image' => function ($model) {
                return Yii::$app->getRequest()->getHostInfo() . ($model->image ? str_replace('\\', '/', $model->image) : '/images/product-default.png');
            },
            'order_id',
            'product_id',
            'key',
            'sku',
            'product_name',
            'extend' => function ($model) {
                $results = [];
                $map = [
                    'names' => 'array',
                    'color' => 'string',
                    'material' => 'string',
                    'size' => 'string',
                    'giftBox' => 'boolean',
                    'beads' => 'integer',
                    'image' => 'url',
                    'other' => 'array',
                ];
                $extend = $model->extend;
                if (!$extend || !is_array($extend)) {
                    $extend = [];
                }

                foreach ($map as $key => $type) {
                    $value = $extend[$key] ?? null;
                    switch ($type) {
                        case 'array':
                            $value = is_array($value) ? $value : [];
                            break;

                        case 'string':
                            $value = is_string($value) ? $value : '';
                            break;

                        case 'integer':
                            $value = (is_numeric($value) && $value > 0) ? intval($value) : 0;
                            break;

                        case 'boolean':
                            $value = is_bool($value) ? boolval($value) : false;
                            break;

                        case 'url':
                            $value = is_string($value) ? $value : '';
                            if ($value) {
                                $value = AppHelper::fixStaticAssetUrl($value);
                            }
                            break;
                    }

                    $results[$key] = $value;
                }

                return $results;
            },
            'ignored' => function ($model) {
                return boolval($model->ignored);
            },
            'quantity',
            'vendor_id',
            'sale_price',
            'cost_price',
            'remark',
        ];
    }

    public function extraFields()
    {
        return ['vendor', 'business', 'order', 'product'];
    }

    /**
     * 所属供应商
     *
     * @return \yii\db\ActiveQuery
     */
    public function getVendor()
    {
        return $this->hasOne(Vendor::class, ['id' => 'vendor_id']);
    }

    /**
     * 业务逻辑
     *
     * @return \yii\db\ActiveQuery
     */
    public function getBusiness()
    {
        $class = get_called_class() . 'Business';
        if (class_exists($class)) {
            return $this->hasOne($class, ['order_item_id' => 'id']);
        } else {
            return null;
        }
    }

    /**
     * 所属订单
     *
     * @return \yii\db\ActiveQuery
     */
    public function getOrder()
    {
        return $this->hasOne(Order::class, ['id' => 'order_id']);
    }

    public function getProduct()
    {
        return $this->hasOne(Product::class, ['id' => 'product_id']);
    }

}

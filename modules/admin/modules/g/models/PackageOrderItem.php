<?php

namespace app\modules\admin\modules\g\models;

/**
 * This is the model class for table "{{%g_package_order_item}}".
 *
 * @property int $id
 * @property int $package_id 包裹
 * @property int $order_id 订单
 * @property int $order_item_id 订单商品
 */
class PackageOrderItem extends \yii\db\ActiveRecord
{

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%g_package_order_item}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['package_id', 'order_id', 'order_item_id'], 'required'],
            [['package_id', 'order_id', 'order_item_id'], 'integer'],
            [['package_id', 'order_id', 'order_item_id'], 'unique', 'targetAttribute' => ['package_id', 'order_id', 'order_item_id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'package_id' => '包裹',
            'order_id' => '订单',
            'order_item_id' => '订单商品',
        ];
    }

}

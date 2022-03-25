<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%g_order}}`.
 */
class m200508_071457_create_g_order_table extends Migration
{

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%g_order}}', [
            'id' => $this->primaryKey(),
            'key' => $this->string(30)->null()->comment("外部单号"),
            'number' => $this->string(50)->notNull()->unique()->comment("订单号"),
            'type' => $this->tinyInteger()->notNull()->defaultValue(0)->comment('类型'),
            'consignee_name' => $this->string(60)->notNull()->comment("收件人姓名"),
            'consignee_mobile_phone' => $this->string(30)->notNull()->comment("收件人电话"),
            'consignee_tel' => $this->string(30)->null()->comment("收件人手机"),
            'country_id' => $this->integer()->notNull()->defaultValue(0)->comment("收件人国家"),
            'consignee_state' => $this->string(50)->notNull()->comment("收件人省/洲"),
            'consignee_city' => $this->string(60)->null()->comment("收件人城市"),
            'consignee_address1' => $this->string(200)->notNull()->comment("收件人地址1"),
            'consignee_address2' => $this->string(200)->null()->comment("收件人地址2"),
            'consignee_postcode' => $this->string(20)->null()->comment("收件人邮编"),
            'total_amount' => $this->decimal(10, 2)->notNull()->defaultValue(0)->comment("订单总金额"),
            'third_party_platform_id' => $this->integer()->notNull()->defaultValue(0)->comment('第三方平台'),
            'third_party_platform_status' => $this->tinyInteger(0)->notNull()->defaultValue(0)->comment("第三方平台状态"),
            'status' => $this->tinyInteger(0)->notNull()->defaultValue(0)->comment("状态"),
            'platform_id' => $this->tinyInteger()->notNull()->defaultValue(0)->comment("平台"),
            'shop_id' => $this->integer()->notNull()->defaultValue(0)->comment("店铺"),
            'product_type' => $this->tinyInteger()->defaultValue(1)->comment("商品类型"),
            'place_order_at' => $this->integer()->notNull()->comment("下单时间"),
            'payment_at' => $this->integer()->comment("付款时间"),
            'cancelled_at' => $this->integer()->comment("取消时间"),
            'cancel_reason' => $this->text()->comment("取消原因"),
            'closed_at' => $this->integer()->comment("关闭时间"),
            'remark' => $this->text()->null()->comment("备注"),
            'created_at' => $this->integer()->notNull()->comment('添加时间'),
            'created_by' => $this->integer()->notNull()->comment('添加人'),
            'updated_at' => $this->integer()->notNull()->comment('更新时间'),
            'updated_by' => $this->integer()->notNull()->comment('更新人'),

        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%g_order}}');
    }

}

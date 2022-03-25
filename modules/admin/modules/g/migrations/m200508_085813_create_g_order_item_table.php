<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%g_order_item}}`.
 */
class m200508_085813_create_g_order_item_table extends Migration
{

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%g_order_item}}', [
            'id' => $this->primaryKey(),
            'order_id' => $this->integer()->notNull()->comment("订单id"),
            'image' => $this->string(100)->notNull()->comment("图片"),
            'product_id' => $this->integer()->notNull()->defaultValue(0)->comment("产品"),
            'sku' => $this->string(100)->notNull()->comment("sku"),
            'product_name' => $this->string(100)->notNull()->comment("商品名"),
            'extend' => $this->json()->comment("扩展"),
            'ignored' => $this->boolean()->notNull()->defaultValue(0)->comment('是否忽略'),
            'quantity' => $this->smallInteger()->defaultValue(1)->notNull()->comment('数量'),
            'vendor_id' => $this->integer()->defaultValue(0)->notNull()->comment('供应商'),
            'sale_price' => $this->decimal(10, 2)->defaultValue(0)->comment("售价"),
            'cost_price' => $this->decimal(10, 2)->defaultValue(0)->comment("成本"),
            'remark' => $this->text()->null()->comment('备注'),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%g_order_item}}');
    }
}

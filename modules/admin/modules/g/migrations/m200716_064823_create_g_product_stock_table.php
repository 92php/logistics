<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%g_product_stock}}`.
 */
class m200716_064823_create_g_product_stock_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%g_product_stock}}', [
            'id' => $this->primaryKey(),
            'product_id' => $this->integer()->notNull()->comment("商品"),
            'warehouse_id' => $this->integer()->notNull()->comment("仓库"),
            'block' => $this->tinyInteger()->notNull()->comment("分区"),
            'rack_id' => $this->integer()->notNull()->comment("货架"),
            'safely_quantity' => $this->integer()->notNull()->defaultValue(0)->comment("安全库存数量"),
            'booking_quantity' => $this->integer()->notNull()->defaultValue(0)->comment("预售库存数量"),
            'trip_quantity' => $this->integer()->notNull()->defaultValue(0)->comment("在途库存数量"),
            'usable_quantity' => $this->integer()->notNull()->defaultValue(0)->comment("可用库存数量"),
            'actual_quantity' => $this->integer()->notNull()->defaultValue(0)->comment("实际库存数量"),
            'price' => $this->decimal(10,2)->notNull()->defaultValue(0)->comment("价格"),
            'total_price' => $this->decimal(10,2)->notNull()->defaultValue(0)->comment("总价"),
            'remark' => $this->text()->null()->comment("备注"),
            'created_at' => $this->integer()->notNull()->comment("创建时间"),
            'updated_at' => $this->integer()->notNull()->comment("修改时间"),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%g_product_stock}}');
    }
}

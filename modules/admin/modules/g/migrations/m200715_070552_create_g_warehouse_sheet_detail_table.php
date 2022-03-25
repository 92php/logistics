<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%g_warehouse_sheet_detail}}`.
 */
class m200715_070552_create_g_warehouse_sheet_detail_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%g_warehouse_sheet_detail}}', [
            'id' => $this->primaryKey(),
            'warehouse_sheet_id' => $this->integer()->notNull()->comment("出入库路单号"),
            'warehouse_id' => $this->integer()->notNull()->comment("仓库"),
            'rack_id' => $this->integer()->notNull()->comment("货架位"),
            'product_id' => $this->integer()->notNull()->comment("商品"),
            'before_stock_quantity' => $this->integer()->notNull()->comment("原库存"),
            'change_quantity' => $this->integer()->notNull()->comment("变动数量"),
            'after_stock_quantity' => $this->integer()->notNull()->comment("新库存"),
            'before_price' => $this->decimal(10, 2)->notNull()->defaultValue(0)->comment("原单价"),
            'change_price' => $this->decimal(10, 2)->notNull()->defaultValue(0)->comment("出入库价格"),
            'after_price' => $this->decimal(10, 2)->notNull()->defaultValue(0)->comment("新单价"),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%g_warehouse_sheet_detail}}');
    }
}

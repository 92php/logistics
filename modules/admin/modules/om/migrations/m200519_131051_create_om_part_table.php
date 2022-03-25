<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%om_part}}`.
 */
class m200519_131051_create_om_part_table extends Migration
{

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%om_part}}', [
            'id' => $this->primaryKey(),
            'sku' => $this->string(40)->notNull()->comment('SKU'),
            'customized' => $this->string(20)->null()->comment("定制名"),
            'is_empty' => $this->boolean()->defaultValue(0)->comment("是否为空"),
            'vendor_id' => $this->integer()->defaultValue(0)->comment("供应商"),
            'order_item_id' => $this->integer()->defaultValue(0)->comment("商品"),
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
        $this->dropTable('{{%om_parts}}');
    }
}

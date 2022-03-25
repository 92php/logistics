<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%om_order_item}}`.
 */
class m200508_074233_create_om_order_item_table extends Migration
{

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%om_order_item}}', [
            'id' => $this->primaryKey(),
            'order_item_id' => $this->integer()->notNull()->comment("订单详情id"),
            'status' => $this->tinyInteger()->defaultValue(0)->comment("状态"),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%om_order_item}}');
    }
}

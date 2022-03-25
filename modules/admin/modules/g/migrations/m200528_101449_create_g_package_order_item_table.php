<?php

use yii\db\Migration;

/**
 * 包裹商品关联
 * Handles the creation of table `{{%g_package_order_item}}`.
 */
class m200528_101449_create_g_package_order_item_table extends Migration
{

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%g_package_order_item}}', [
            'id' => $this->primaryKey(),
            'package_id' => $this->integer()->notNull()->comment('包裹'),
            'order_id' => $this->integer()->notNull()->comment('订单'),
            'order_item_id' => $this->integer()->notNull()->comment('订单商品'),
        ]);
        $this->createIndex('package_id_order_item_id', '{{%g_package_order_item}}', [
            'package_id',
            'order_item_id'
        ], true);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%g_package_order_item}}');
    }

}

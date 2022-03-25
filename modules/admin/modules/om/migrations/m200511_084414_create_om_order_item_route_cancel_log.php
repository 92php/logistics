<?php

namespace app\migrations;

use yii\db\Migration;

/**
 * Handles the creation of table `{{%om_order_item_route_cancel_log}}`.
 */
class m200511_084414_create_om_order_item_route_cancel_log extends Migration
{

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%om_order_item_route_cancel_log}}', [
            'id' => $this->primaryKey(),
            'order_item_route_id' => $this->string(20)->unique()->comment("orderItemRoute Id"),
            'canceled_reason' => $this->string()->notNull()->comment("取消原因"),
            'canceled_quantity' => $this->smallInteger()->notNull()->comment("取消数量"),
            'type' => $this->smallInteger()->notNull()->defaultValue(0)->comment("取消类型"),
            'canceled_at' => $this->integer()->notNull()->comment('取消时间'),
            'canceled_by' => $this->integer()->notNull()->comment('取消人'),
            'confirmed_status' => $this->integer()->defaultValue(0)->comment("确认状态"),
            'confirmed_message' => $this->text()->null()->comment("确认反馈消息"),
            'confirmed_at' => $this->integer()->null()->comment('确认时间'),
            'confirmed_by' => $this->integer()->null()->comment('确认人'),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%om_order_item_route_cancel_log}}');
    }
}

<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%om_order_item_route}}`.
 */
class m200506_020707_create_om_order_item_route_table extends Migration
{

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%om_order_item_route}}', [
            'id' => $this->primaryKey(),
            'waybill_number' => $this->integer()->null()->comment("运单号"),
            'package_id' => $this->integer()->null()->comment("包裹"),
            'order_item_id' => $this->integer()->notNull()->comment("订单详情id"),
            'place_order_at' => $this->integer()->notNull()->comment("下单时间"),
            'place_order_by' => $this->integer()->notNull()->comment("下单人"),
            'vendor_id' => $this->integer()->notNull()->comment("供应商"),
            'receipt_at' => $this->integer()->null()->comment("接单时间"),
            'receipt_status' => $this->tinyInteger()->notNull()->comment("接单状态"),
            'production_at' => $this->integer()->null()->comment("生产时间"),
            'vendor_deliver_at' => $this->integer()->null()->comment("供应商发货时间"),
            'delivery_status' => $this->tinyInteger()->notNull()->defaultValue(0)->comment("供应商发货状态"),
            'receiving_at' => $this->integer()->null()->comment("收货时间"),
            'receiving_status' => $this->tinyInteger()->notNull()->defaultValue(0)->comment("收货状态"),
            'inspection_at' => $this->integer()->null()->comment("质检时间"),
            'inspection_status' => $this->integer()->null()->comment("质检时间"),
            'inspection_image' => $this->string(50)->null()->comment("质检图片"),
            'warehousing_at' => $this->integer()->null()->comment("入库时间"),
            'is_reissue' => $this->boolean()->defaultValue(0)->comment("是否补发"),
            'quantity' => $this->smallInteger()->defaultValue(0)->comment("数量"),
            'status' => $this->boolean()->defaultValue(0)->comment("状态"),
            'reason' => $this->text()->null()->comment("拒接原因"),
            'is_accord_with' => $this->boolean()->defaultValue(0)->comment("是否符合质检标准"),
            'feedback' => $this->text()->null()->comment("反馈"),
            'is_information_match' => $this->boolean()->defaultValue(0)->comment("是否信息匹配"),
            'information_feedback' => $this->text()->null()->comment("信息匹配反馈"),
            'inspection_number' => $this->integer()->null()->comment("已质检数量"),
            'cost_price' => $this->decimal(10, 2)->defaultValue(0)->comment("成本"),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%om_order_item_route}}');
    }
}

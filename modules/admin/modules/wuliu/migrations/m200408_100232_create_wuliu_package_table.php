<?php

use yii\db\Migration;

/**
 * 包裹管理
 * Handles the creation of table `{{%wuliu_package}}`.
 */
class m200408_100232_create_wuliu_package_table extends Migration
{

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%wuliu_package}}', [
            'id' => $this->primaryKey(),
            'package_id' => $this->bigInteger()->notNull()->unique()->comment('包裹编号'),
            'package_number' => $this->string(30)->notNull()->unique()->comment('包裹号'),
            'order_number' => $this->string(100)->notNull()->comment('订单号'),
            'line_id' => $this->integer()->notNull()->comment('线路'),
            'waybill_number' => $this->string(30)->notNull()->unique()->comment('运单号'),
            'country_id' => $this->integer()->notNull()->defaultValue(0)->comment('收件国家'),
            'weight' => $this->integer()->notNull()->defaultValue(0)->comment('重量'),
            'freight_cost' => $this->decimal(7, 2)->notNull()->defaultValue(0)->comment('运费'),
            'dxm_account_id' => $this->integer()->notNull()->comment('店小秘帐号'),
            'shop_name' => $this->string(30)->notNull()->comment('店铺名称'),
            'delivery_datetime' => $this->integer()->null()->comment('发货时间'),
            'estimate_days' => $this->tinyInteger()->notNull()->defaultValue(0)->comment('预计天数'),
            'final_days' => $this->tinyInteger()->notNull()->defaultValue(0)->comment('最终天数'),
            'logistics_query_raw_results' => $this->text()->comment('物流查询结果'),
            'last_check_datetime' => $this->integer()->null()->comment('最后检测时间'),
            'sync_status' => $this->tinyInteger()->notNull()->defaultValue(0)->comment('同步状态'),
            'status' => $this->tinyInteger()->notNull()->defaultValue(0)->comment('状态'),
            'remark' => $this->text()->comment('备注'),
            'created_at' => $this->integer()->comment("创建时间"),
            'created_by' => $this->integer()->comment("创建人"),
            'updated_at' => $this->integer()->comment("修改时间"),
            'updated_by' => $this->integer()->comment("修改人"),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%wuliu_package}}');
    }

}

<?php

use yii\db\Migration;

/**
 * 包裹
 * Handles the creation of table `{{%g_package}}`.
 */
class m200528_101357_create_g_package_table extends Migration
{

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%g_package}}', [
            'id' => $this->primaryKey(),
            'key' => $this->string(30)->null()->comment("包裹 Key"),
            'number' => $this->string(30)->unique()->notNull()->comment("包裹号"),
            'country_id' => $this->integer()->notNull()->defaultValue(0)->comment("所属国家"),
            'waybill_number' => $this->string(40)->comment("运单号"),
            'weight' => $this->integer()->notNull()->defaultValue(0)->comment('重量'),
            'freight_cost' => $this->decimal(7, 2)->notNull()->defaultValue(0)->comment('运费'),
            'delivery_datetime' => $this->integer()->null()->comment('发货时间'),
            'weight_datetime' => $this->integer()->null()->comment('称重时间'),
            'reference_weight' => $this->integer()->notNull()->defaultValue(0)->comment('参考重量'),
            'reference_freight_cost' => $this->decimal(7, 2)->notNull()->defaultValue(0)->comment('参考运费'),
            'logistics_line_id' => $this->integer()->notNull()->defaultValue(0)->comment('物流线路'),
            'logistics_query_raw_results' => $this->text()->comment('物流查询结果'),
            'logistics_last_check_datetime' => $this->integer()->null()->comment('最后检测时间'),
            'estimate_days' => $this->tinyInteger()->notNull()->defaultValue(0)->comment('预计天数'),
            'final_days' => $this->tinyInteger()->notNull()->defaultValue(0)->comment('最终天数'),
            'sync_status' => $this->tinyInteger()->notNull()->defaultValue(0)->comment('同步状态'),
            'shop_id' => $this->integer()->notNull()->comment('店铺'),
            'third_party_platform_id' => $this->integer()->notNull()->defaultValue(0)->comment('第三方平台'),
            'third_party_platform_status' => $this->tinyInteger(0)->notNull()->defaultValue(0)->comment("第三方平台状态"),
            'status' => $this->tinyInteger()->notNull()->defaultValue(0)->comment('状态'),
            'remark' => $this->text()->comment('备注'),
            'created_at' => $this->integer()->notNull()->comment("创建时间"),
            'created_by' => $this->integer()->notNull()->comment("创建人"),
            'updated_at' => $this->integer()->notNull()->comment("修改时间"),
            'updated_by' => $this->integer()->notNull()->comment("修改人"),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%g_package}}');
    }

}

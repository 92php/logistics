<?php

use yii\db\Migration;

/**
 * SKU 供应商匹配管理
 * Handles the creation of table `{{%om_sku_vendor}}`.
 */
class m200508_093435_create_om_sku_vendor_table extends Migration
{

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%om_sku_vendor}}', [
            'id' => $this->primaryKey(),
            'ordering' => $this->tinyInteger()->notNull()->defaultValue(1)->comment('排序'),
            'sku' => $this->string(40)->notNull()->unique()->comment('SKU'),
            'vendor_id' => $this->integer()->notNull()->comment('供应商'),
            'cost_price' => $this->money(10, 2)->notNull()->defaultValue(0)->comment('成本价'),
            'production_min_days' => $this->tinyInteger()->notNull()->defaultValue(1)->comment('生产最小天数'),
            'production_max_days' => $this->tinyInteger()->notNull()->defaultValue(1)->comment('生产最大天数'),
            'enabled' => $this->boolean()->defaultValue(1)->notNull()->comment('激活'),
            'remark' => $this->text()->comment('备注'),
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
        $this->dropTable('{{%om_sku_vendor}}');
    }

}

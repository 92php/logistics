<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%g_warehouse_sheet}}`.
 */
class m200715_063004_create_g_warehouse_sheet_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%g_warehouse_sheet}}', [
            'id' => $this->primaryKey(),
            'warehouse_id' => $this->integer()->notNull()->defaultValue(0)->comment("仓库"),
            'number' => $this->string(30)->notNull()->comment("流水单号"),
            'type' => $this->tinyInteger()->notNull()->defaultValue(0)->comment("类型"),
            'method' => $this->tinyInteger()->notNull()->defaultValue(0)->comment("出入库方式"),
            'change_datetime' => $this->integer()->notNull()->comment("商品出入库时间"),
            'operation_member_id' => $this->integer()->notNull()->comment("操作人"),
            'operation_datetime' => $this->integer()->notNull()->comment("操作时间"),
            'remark' => $this->text()->null()->comment("备注"),
            'created_at' => $this->integer()->notNull()->comment("添加人"),
            'created_by' => $this->integer()->notNull()->comment("添加时间"),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%g_warehouse_sheet}}');
    }
}

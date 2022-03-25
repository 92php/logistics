<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%g_rack}}`.
 */
class m200714_064409_create_g_rack_table extends Migration
{

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%g_rack}}', [
            'id' => $this->primaryKey(),
            'warehouse_id' => $this->integer()->notNull()->comment("仓库"),
            'block' => $this->tinyInteger()->notNull()->defaultValue(0)->comment("仓库"),
            'number' => $this->string(30)->notNull()->comment("货架编号"),
            'priority' => $this->integer()->notNull()->defaultValue(0)->comment("拣货权重"),
            'remark' => $this->text()->null()->comment("备注"),
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
        $this->dropTable('{{%g_rack}}');
    }
}

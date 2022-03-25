<?php

use yii\db\Migration;

/**
 * 标签管理
 * Handles the creation of table `{{%g_tag}}`.
 */
class m200710_084852_create_g_tag_table extends Migration
{

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%g_tag}}', [
            'id' => $this->primaryKey(),
            'type' => $this->tinyInteger()->notNull()->defaultValue(0)->comment('类型'),
            'parent_id' => $this->integer()->notNull()->defaultValue(0)->comment('上级标签'),
            'name' => $this->string(30)->notNull()->comment('标签名称'),
            'ordering' => $this->tinyInteger()->notNull()->defaultValue(0)->comment('排序'),
            'enabled' => $this->boolean()->notNull()->defaultValue(1)->comment('激活'),
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
        $this->dropTable('{{%g_tag}}');
    }

}

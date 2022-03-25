<?php

namespace yii\queue\db\migrations;

use yii\db\Migration;

/**
 * Handles the creation of table `{{%om_package}}`.
 */
class m200509_012104_create_om_package_table extends Migration
{

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%om_package}}', [
            'id' => $this->primaryKey(),
            'number' => $this->string(20)->unique()->comment("包裹号"),
            'title' => $this->string()->notNull()->comment("包裹名称"),
            'items_quantity' => $this->integer()->defaultValue(0)->comment("物品数量"),
            'remaining_items_quantity' => $this->integer()->defaultValue(0)->comment("待寄送物品数量"),
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
        $this->dropTable('{{%om_package}}');
    }
}

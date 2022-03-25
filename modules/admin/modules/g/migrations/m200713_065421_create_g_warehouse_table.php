<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%g_warehouse}}`.
 */
class m200713_065421_create_g_warehouse_table extends Migration
{

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%g_warehouse}}', [
            'id' => $this->primaryKey(),
            'name' => $this->string(30)->notNull()->comment('仓库名称'),
            'address' => $this->string(100)->notNull()->comment('地址'),
            'linkman' => $this->string(10)->notNull()->comment('联系人'),
            'tel' => $this->string(10)->notNull()->comment('电话'),
            'enabled' => $this->boolean()->notNull()->defaultValue(1)->comment('激活'),
            'remark' => $this->text()->null()->comment('备注'),
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
        $this->dropTable('{{%g_warehouse}}');
    }

}

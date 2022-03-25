<?php

use yii\db\Migration;

/**
 * 客户管理
 * Handles the creation of table `{{%g_customer}}`.
 */
class m200720_012716_create_g_customer_table extends Migration
{

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%g_customer}}', [
            'id' => $this->primaryKey(),
            'platform_id' => $this->tinyInteger()->notNull()->comment('所属平台'),
            'key' => $this->string(30)->null()->comment('外部系统编号'),
            'email' => $this->string(100)->null()->comment('邮箱'),
            'first_name' => $this->string(20)->notNull()->comment('姓'),
            'last_name' => $this->string(20)->comment('名'),
            'phone' => $this->string(30)->null()->comment('联系电话'),
            'currency' => $this->string(3)->null()->comment('货币'),
            'remark' => $this->text()->null()->comment('备注'),
            'status' => $this->tinyInteger()->notNull()->comment('状态'),
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
        $this->dropTable('{{%g_customer}}');
    }

}

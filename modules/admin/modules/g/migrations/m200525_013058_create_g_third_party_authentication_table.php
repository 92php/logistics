<?php

use yii\db\Migration;

/**
 * 第三方平台认证管理
 * Handles the creation of table `{{%g_third_party_authentication}}`.
 */
class m200525_013058_create_g_third_party_authentication_table extends Migration
{

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%g_third_party_authentication}}', [
            'id' => $this->primaryKey(),
            'platform_id' => $this->smallInteger()->notNull()->comment('所属平台'),
            'name' => $this->string(100)->notNull()->comment('名称'),
            'authentication_config' => $this->json()->null()->comment('访问配置'),
            'enabled' => $this->boolean()->defaultValue(1)->notNull()->comment('激活'),
            'remark' => $this->text()->null()->comment('备注'),
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
        $this->dropTable('{{%g_third_party_authentication}}');
    }

}

<?php

use yii\db\Migration;

/**
 * 店铺管理
 * Handles the creation of table `{{%g_shop}}`.
 *

 */
class m200523_054442_create_g_shop_table extends Migration
{

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%g_shop}}', [
            'id' => $this->primaryKey(),
            'organization_id' => $this->smallInteger()->notNull()->comment('所属组织'),
            'platform_id' => $this->smallInteger()->notNull()->comment('所属平台'),
            'name' => $this->string(100)->notNull()->comment('店铺名称'),
            'url' => $this->string(200)->notNull()->comment('访问地址'),
            'product_type' => $this->tinyInteger()->defaultValue(1)->notNull()->comment('商品类型'),
            'third_party_authentication_id' => $this->integer()->notNull()->defaultValue(0)->comment('第三方认证配置'),
            'third_party_sign' => $this->string(200)->null()->comment('第三方标记'),
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
        $this->dropTable('{{%g_shop}}');
    }

}

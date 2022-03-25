<?php

use yii\db\Migration;

/**
 * 供应商会员关联
 * Handles the creation of table `{{%g_vendor_member}}`.
 */
class m200529_081230_create_g_vendor_member_table extends Migration
{

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%g_vendor_member}}', [
            'id' => $this->primaryKey(),
            'vendor_id' => $this->integer()->notNull()->comment('供应商'),
            'member_id' => $this->integer()->notNull()->comment('会员'),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%g_vendor_member}}');
    }

}

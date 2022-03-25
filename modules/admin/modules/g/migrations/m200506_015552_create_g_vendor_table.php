<?php

use yii\db\Migration;

/**
 * 供应商管理
 * Handles the creation of table `{{%g_vendor}}`.
 */
class m200506_015552_create_g_vendor_table extends Migration
{

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%g_vendor}}', [
            'id' => $this->primaryKey(),
            'name' => $this->string(30)->notNull()->comment('供应商名称'),
            'address' => $this->string(100)->notNull()->comment('地址'),
            'tel' => $this->string(13)->comment('联系电话'),
            'linkman' => $this->string(10)->notNull()->comment('联系人'),
            'mobile_phone' => $this->string(11)->notNull()->comment('手机号码'),
            'receipt_duration' => $this->float()->defaultValue(0)->comment('接单时长'),
            'production' => $this->integer()->notNull()->comment("生产量/天"),
            'credibility' => $this->integer()->defaultValue(100)->comment('信誉度'),
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
        $this->dropTable('{{%g_vendor}}');
    }

}

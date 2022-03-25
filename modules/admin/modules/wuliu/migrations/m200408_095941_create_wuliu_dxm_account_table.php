<?php

use yii\db\Migration;

/**
 * 店小秘帐号管理
 * Handles the creation of table `{{%wuliu_dxm_account}}`.
 */
class m200408_095941_create_wuliu_dxm_account_table extends Migration
{

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%wuliu_dxm_account}}', [
            'id' => $this->primaryKey(),
            'username' => $this->string(30)->notNull()->unique()->comment('用户名'),
            'password' => $this->string(30)->notNull()->comment('密码'),
            'company_id' => $this->integer()->notNull()->comment('物流公司'),
            'platform_id' => $this->integer()->notNull()->defaultValue(0)->comment('所属团队'),
            'is_valid' => $this->boolean()->notNull()->defaultValue(0)->comment('是否有效'),
            'cookies' => $this->text()->null()->comment('Cookies'),
            'remark' => $this->text()->comment('备注'),
            'created_at' => $this->integer()->comment("创建时间"),
            'created_by' => $this->integer()->comment("创建人"),
            'updated_at' => $this->integer()->comment("修改时间"),
            'updated_by' => $this->integer()->comment("修改人"),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%wuliu_dxm_account}}');
    }

}

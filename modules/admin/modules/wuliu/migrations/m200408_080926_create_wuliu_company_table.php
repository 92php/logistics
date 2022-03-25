<?php

use yii\db\Migration;

/**
 * 物流公司
 * Handles the creation of table `{{%wuliu_company}}`.
 */
class m200408_080926_create_wuliu_company_table extends Migration
{

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%wuliu_company}}', [
            'id' => $this->primaryKey(),
            'code' => $this->string(20)->unique()->notNull()->comment('代码'),
            'name' => $this->string(30)->notNull()->comment('公司名称'),
            'website_url' => $this->string(100)->notNull()->comment('网站'),
            'linkman' => $this->string(20)->notNull()->comment('联系人'),
            'mobile_phone' => $this->string(30)->notNull()->comment('手机号码'),
            'enabled' => $this->boolean()->notNull()->defaultValue(1)->comment('激活'),
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
        $this->dropTable('{{%wuliu_company}}');
    }

}

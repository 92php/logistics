<?php

use yii\db\Migration;

/**
 * 物流公司线路
 * Handles the creation of table `{{%wuliu_company_line}}`.
 */
class m200408_094304_create_wuliu_company_line_table extends Migration
{

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%wuliu_company_line}}', [
            'id' => $this->primaryKey(),
            'company_id' => $this->integer()->notNull()->comment('所属公司'),
            'name_prefix' => $this->string(30)->null()->comment('线路名称前缀'),
            'name' => $this->string(100)->notNull()->comment('线路名称'),
            'estimate_days' => $this->tinyInteger()->notNull()->defaultValue(0)->comment('预计天数'),
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
        $this->dropTable('{{%wuliu_company_line}}');
    }

}

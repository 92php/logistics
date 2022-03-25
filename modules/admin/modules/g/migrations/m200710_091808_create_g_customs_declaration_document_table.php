<?php

use yii\db\Migration;

/**
 * 报关信息
 * Handles the creation of table `{{%g_customs_declaration_document}}`.
 */
class m200710_091808_create_g_customs_declaration_document_table extends Migration
{

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%g_customs_declaration_document}}', [
            'id' => $this->primaryKey(),
            'code' => $this->string(30)->null()->comment('海关编码'),
            'chinese_name' => $this->string(200)->notNull()->comment('商品中文名称'),
            'english_name' => $this->string(300)->notNull()->comment('商品英文名称'),
            'weight' => $this->integer()->notNull()->defaultValue(0)->comment('申报重量'),
            'amount' => $this->decimal(10, 2)->notNull()->defaultValue(0)->comment('申报金额'),
            'danger_level' => $this->tinyInteger()->notNull()->defaultValue(0)->comment('危险等级'),
            'default' => $this->boolean()->notNull()->defaultValue(0)->comment('默认'),
            'enabled' => $this->boolean()->notNull()->defaultValue(1)->comment('激活'),
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
        $this->dropTable('{{%g_customs_declaration_document}}');
    }

}

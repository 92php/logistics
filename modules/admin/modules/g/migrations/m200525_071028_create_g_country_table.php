<?php

use yii\db\Migration;

/**
 * 国家
 * Handles the creation of table `{{%g_country}}`.
 */
class m200525_071028_create_g_country_table extends Migration
{

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%g_country}}', [
            'id' => $this->primaryKey(),
            'region_id' => $this->smallInteger()->notNull()->comment('地区'),
            'abbreviation' => $this->string(6)->notNull()->unique()->comment('国家简称'),
            'chinese_name' => $this->string(30)->notNull()->unique()->comment('中文名称'),
            'english_name' => $this->string(30)->notNull()->unique()->comment('英文名称'),
            'enabled' => $this->boolean()->notNull()->defaultValue(1)->comment('激活'),
            'created_at' => $this->integer()->notNull()->comment("创建时间"),
            'created_by' => $this->integer()->notNull()->comment("创建人"),
            'updated_at' => $this->integer()->comment("修改时间"),
            'updated_by' => $this->integer()->comment("修改人"),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%g_country}}');
    }

}

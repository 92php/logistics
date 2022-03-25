<?php

use yii\db\Migration;

/**
 * 运费模板
 * Handles the creation of table `{{%wuliu_freight_template}}`.
 */
class m200408_111401_create_wuliu_freight_template_table extends Migration
{

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%wuliu_freight_template}}', [
            'id' => $this->primaryKey(),
            'company_id' => $this->integer()->notNull()->comment('物流公司'),
            'name' => $this->string(30)->notNull()->comment('名称'),
            'fee_mode' => $this->smallInteger()->notNull()->defaultValue(1)->comment('计费方式'),
            'enabled' => $this->tinyInteger()->notNull()->defaultValue(1)->comment('激活'),
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
        $this->dropTable('{{%wuliu_freight_template}}');
    }

}

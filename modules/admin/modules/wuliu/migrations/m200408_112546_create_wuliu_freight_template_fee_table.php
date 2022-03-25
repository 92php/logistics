<?php

use yii\db\Migration;

/**
 * 运费模板计费方式
 * Handles the creation of table `{{%wuliu_freight_template_fee}}`.
 */
class m200408_112546_create_wuliu_freight_template_fee_table extends Migration
{

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%wuliu_freight_template_fee}}', [
            'id' => $this->primaryKey(),
            'template_id' => $this->integer()->notNull()->comment('模板'),
            'line_id' => $this->integer()->notNull()->comment('物流线路'),
            'min_weight' => $this->integer()->notNull()->comment('最小重量'),
            'max_weight' => $this->integer()->notNull()->comment('最大重量'),
            'first_weight' => $this->integer()->notNull()->defaultValue(1)->comment('首重'),
            'first_fee' => $this->decimal(7, 3)->notNull()->comment('首重费用'),
            'continued_weight' => $this->integer()->notNull()->defaultValue(1)->comment('续重'),
            'continued_fee' => $this->decimal(7, 3)->notNull()->comment('续重费用'),
            'fixed_fee' => $this->decimal(7, 3)->defaultValue(0)->comment('固定费用'),
            'base_fee' => $this->decimal(7, 3)->notNull()->comment('挂号费'),
            'freight_fee_rate' => $this->integer()->defaultValue(0)->comment('运费基数折扣率'),
            'base_fee_rate' => $this->integer()->defaultValue(0)->comment('挂号费折扣率'),
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
        $this->dropTable('{{%wuliu_freight_template_fee}}');
    }

}

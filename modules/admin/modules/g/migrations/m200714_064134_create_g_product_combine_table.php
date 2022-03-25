<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%g_product_combine}}`.
 */
class m200714_064134_create_g_product_combine_table extends Migration
{

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%g_product_combine}}', [
            'id' => $this->primaryKey(),
            'product_id' => $this->integer()->notNull()->comment('所属产品'),
            'child_product_id' => $this->integer()->notNull()->comment('子级产品'),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%g_product_combine}}');
    }
}

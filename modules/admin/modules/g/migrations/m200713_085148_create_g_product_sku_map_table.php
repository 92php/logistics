<?php

use yii\db\Migration;

/**
 * 商品 sku 映射关系
 * Handles the creation of table `{{%g_product_sku_map}}`.
 */
class m200713_085148_create_g_product_sku_map_table extends Migration
{

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%g_product_sku_map}}', [
            'id' => $this->primaryKey(),
            'product_id' => $this->integer()->notNull()->comment('商品'),
            'value' => $this->string(128)->notNull()->comment('商品 SKU'),
        ]);
        $this->createIndex('product_id_value', '{{%g_product_sku_map}}', ['product_id', 'value'], true);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%g_product_sku_map}}');
    }

}

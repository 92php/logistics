<?php

use yii\db\Migration;

/**
 * 商品管理
 * Handles the creation of table `{{%g_product}}`.
 */
class m200605_073403_create_g_product_table extends Migration
{

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%g_product}}', [
            'id' => $this->primaryKey(),
            'category_id' => $this->integer()->notNull()->defaultValue(0)->comment('分类'),
            'type' => $this->tinyInteger(1)->defaultValue(0)->comment("商品类型"),
            'sale_method ' => $this->tinyInteger(1)->defaultValue(0)->comment("销售方式"),
            'key' => $this->string(128)->notNull()->unique()->comment('Key'),
            'sku' => $this->string(128)->notNull()->unique()->comment('SKU'),
            'chinese_name' => $this->string(200)->notNull()->comment('商品中文名称'),
            'english_name' => $this->string(300)->notNull()->comment('商品英文名称'),
            'weight' => $this->integer()->notNull()->defaultValue(0)->comment('重量'),
            'size_length' => $this->float()->notNull()->defaultValue(0)->comment('长'),
            'size_width' => $this->float()->notNull()->defaultValue(0)->comment('宽'),
            'size_height' => $this->float()->notNull()->defaultValue(0)->comment('高'),
            'allow_offset_weight' => $this->float()->notNull()->defaultValue(0)->comment('允许称重误差'),
            'stock_quantity' => $this->integer()->notNull()->defaultValue(0)->comment('库存数量'),
            'status' => $this->tinyInteger(1)->defaultValue(0)->comment("商品状态"),
            'development_member_id' => $this->integer()->notNull()->defaultValue(0)->comment('开发员'),
            'purchase_member_id' => $this->integer()->notNull()->defaultValue(0)->comment('采购员'),
            'price' => $this->decimal(10, 2)->notNull()->defaultValue(0)->comment('价格'),
            'image' => $this->string(100)->null()->comment('产品图'),
            'purchase_reference_price' => $this->decimal(10, 2)->notNull()->defaultValue(0)->comment('采购参考价'),
            'qc_description' => $this->decimal(10, 2)->null()->comment('质检说明'),
            'customs_declaration_document_id' => $this->integer()->notNull()->defaultValue(0)->comment('报关信息'),
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
        $this->dropTable('{{%g_product}}');
    }

}

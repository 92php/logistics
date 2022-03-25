<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%g_product_image}}`.
 */
class m200714_063315_create_g_product_image_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%g_product_image}}', array(
            'id' => $this->primaryKey(),
            'product_id' => $this->integer()->notNull()->comment('所属产品'),
            'title' => $this->string(100)->comment('标题'),
            'path' => $this->string(100)->comment('图片'),
            'ordering' => $this->smallInteger()->notNull()->defaultValue(0)->comment('排序'),
            'created_at' => $this->integer()->notNull()->comment('添加时间'),
            'created_by' => $this->integer()->notNull()->comment('添加人'),
            'updated_at' => $this->integer()->notNull()->comment('更新时间'),
            'updated_by' => $this->integer()->notNull()->comment('更新人'),
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%g_product_image}}');
    }
}

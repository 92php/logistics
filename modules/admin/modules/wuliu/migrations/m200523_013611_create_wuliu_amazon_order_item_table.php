<?php

namespace app\modules\admin\modules\wuliu\migrations;

use yii\db\Migration;

/**
 * Class m200523_013611_create_wuliu_amazon_order_table
 */
class m200523_013611_create_wuliu_amazon_order_item_table extends Migration
{

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%wuliu_amazon_order_item}}', [
            'id' => $this->primaryKey(),
            'order_id' => $this->string(30)->notNull()->comment('订单号'),
            'product_name' => $this->string(50)->notNull()->comment('产品'),
            'product_image' => $this->string(200)->comment('中文名称'),
            'product_quantity' => $this->smallInteger()->notNull()->comment('产品数量'),
            'size' => $this->string(50)->null()->comment('尺寸'),
            'color' => $this->string(50)->null()->comment('颜色'),
            'customized' => $this->string(50)->null()->comment('定制内容'),
            'remark' => $this->text()->null()->comment('备注'),
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
        $this->dropTable('{{%wuliu_amazon_order_item}}');
    }

}

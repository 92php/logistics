<?php

use yii\db\Migration;

/**
 * 客户地址管理
 * Handles the creation of table `{{%g_customer_address}}`.
 */
class m200720_012743_create_g_customer_address_table extends Migration
{

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%g_customer_address}}', [
            'id' => $this->primaryKey(),
            'customer_id' => $this->tinyInteger()->notNull()->comment('客户'),
            'key' => $this->string(30)->null()->comment('外部系统编号'),
            'first_name' => $this->string(20)->notNull()->comment('姓'),
            'last_name' => $this->string(20)->null()->comment('名'),
            'company' => $this->string(200)->null()->comment('公司'),
            'address1' => $this->string(200)->null()->comment('地址 1'),
            'address2' => $this->string(200)->null()->comment('地址 2'),
            'country_id' => $this->integer()->notNull()->defaultValue(0)->comment('国家'),
            'province' => $this->string(50)->null()->comment('省/州'),
            'city' => $this->string(50)->null()->comment('城市'),
            'zip' => $this->string(20)->null()->comment('邮编'),
            'phone' => $this->string(30)->null()->comment('联系电话'),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%g_customer_address}}');
    }

}

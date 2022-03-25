<?php

use yii\db\Migration;

/**
 * 线路路由
 * Handles the creation of table `{{%wuliu_company_line_route}}`.
 */
class m200408_094312_create_wuliu_company_line_route_table extends Migration
{

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%wuliu_company_line_route}}', [
            'id' => $this->primaryKey(),
            'line_id' => $this->integer()->notNull()->comment('所属线路'),
            'step' => $this->tinyInteger()->notNull()->defaultValue(1)->comment('步骤'),
            'event' => $this->string(30)->notNull()->comment('事件'),
            'detection_keyword' => $this->string(200)->notNull()->comment('判断依据'),
            'estimate_days' => $this->tinyInteger()->notNull()->defaultValue(0)->comment('预计天数'),
            'package_status' => $this->tinyInteger()->notNull()->defaultValue(0)->comment('包裹状态'),
            'enabled' => $this->boolean()->notNull()->defaultValue(1)->comment('激活'),
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
        $this->dropTable('{{%wuliu_company_line_route}}');
    }

}

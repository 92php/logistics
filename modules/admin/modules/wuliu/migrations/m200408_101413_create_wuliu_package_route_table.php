<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%wuliu_package_route}}`.
 */
class m200408_101413_create_wuliu_package_route_table extends Migration
{

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%wuliu_package_route}}', [
            'id' => $this->primaryKey(),
            'package_id' => $this->integer()->notNull()->comment('包裹'),
            'line_route_id' => $this->integer()->notNull()->comment('线路路由'),
            'compute_method' => $this->smallInteger()->notNull()->defaultValue(0)->comment('估算方式'),
            'compute_reference_value' => $this->smallInteger()->notNull()->defaultValue(0)->comment('估算参考值'),
            'begin_datetime' => $this->integer()->null()->comment('开始时间'),
            'plan_datetime' => $this->integer()->notNull()->comment('预测时间'),
            'plan_datetime_is_changed' => $this->boolean()->notNull()->defaultValue(0)->comment('是否修改'),
            'end_datetime' => $this->integer()->null()->comment('结束时间'),
            'take_minutes' => $this->integer()->notNull()->comment('耗费时间'),
            'status' => $this->tinyInteger()->notNull()->defaultValue(0)->comment('状态'),
            'process_status' => $this->tinyInteger()->notNull()->defaultValue(0)->comment('处理状态'),
            'process_member_id' => $this->integer()->notNull()->defaultValue(0)->comment('处理人'),
            'process_datetime' => $this->integer()->notNull()->comment('处理时间'),
            'remark' => $this->text()->comment('备注'),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%wuliu_package_route}}');
    }
}

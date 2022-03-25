<?php

use yii\db\Migration;

/**
 * 同步任务管理
 * Handles the creation of table `{{%sync_task}}`.
 */
class m200618_055838_create_sync_task_table extends Migration
{

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%g_sync_task}}', [
            'id' => $this->primaryKey(),
            'shop_id' => $this->integer()->notNull()->comment('店铺'),
            'begin_date' => $this->integer()->notNull()->comment('开始日期'),
            'end_date' => $this->integer()->notNull()->comment('结束日期'),
            'priority' => $this->tinyInteger()->notNull()->defaultValue(10)->comment('优先级'),
            'start_datetime' => $this->integer()->null()->comment('启动时间'),
            'status' => $this->tinyInteger()->notNull()->defaultValue(0)->comment('状态'),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%g_sync_task}}');
    }

}

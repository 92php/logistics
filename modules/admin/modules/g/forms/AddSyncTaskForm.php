<?php

namespace app\modules\admin\modules\g\forms;

use app\models\Constant;
use app\modules\admin\modules\g\models\SyncTask;
use DateTime;
use Yii;
use yii\base\Model;

/**
 * 站点添加同步任务表单
 *
 * @package app\modules\admin\modules\g\forms

 */
class AddSyncTaskForm extends Model
{

    /**
     * @var array 同步的店铺
     */
    public $shop_ids;

    /**
     * @var string 同步开始时间
     */
    public $begin_date;

    /**
     * @var string 同步结束时间
     */
    public $end_date;

    /**
     * @var int 优先级
     */
    public $priority;

    public function rules()
    {
        return [
            [['shop_ids', 'begin_date', 'end_date', 'priority'], 'required'],
            ['shop_ids', 'safe'],
            [['begin_date', 'end_date'], 'date', 'format' => 'php:Y-m-d'],
            ['priority', 'integer'],
            ['priority', 'default', 'value' => 10],
        ];
    }

    /**
     * 保存导入的数据
     *
     * @return bool
     * @throws \yii\db\Exception
     * @throws \Exception
     */
    public function save()
    {
        $db = Yii::$app->getDb();
        $cmd = $db->createCommand();
        $shopIds = is_array($this->shop_ids) ? $this->shop_ids : explode(',', $this->shop_ids);
        $histories = $db->createCommand('SELECT [[id]], [[shop_id]], [[begin_date]], [[end_date]] FROM {{%g_sync_task}} WHERE [[status]] = :status', [
            ':status' => SyncTask::STATUS_PENDING,
        ])->queryAll();
        $insertRows = [];
        $updateRows = [];
        foreach ($shopIds as $shopId) {
            $shopId = intval($shopId);
            $exists = $db->createCommand('SELECT COUNT(*) FROM {{%g_shop}} WHERE [[id]] = :id AND [[enabled]] = :enabled', [
                ':id' => $shopId,
                ':enabled' => Constant::BOOLEAN_TRUE,
            ])->queryScalar();
            if (!$exists) {
                continue;
            }
            $beginDate = (new DateTime($this->begin_date))->setTime(0, 0, 0)->getTimestamp();
            $endDate = (new DateTime($this->end_date))->setTime(0, 0, 0)->getTimestamp();

            $exists = false;
            foreach ($histories as $history) {
                if ($history['shop_id'] == $shopId) {
                    $exists = true;
                    if ($history['begin_date'] == $beginDate && $history['end_date'] == $endDate) {
                        break;
                    }
                    if ($history['begin_date'] > $beginDate) {
                        $updateRows[$shopId]['begin_date'] = $beginDate;
                    }
                    if ($history['end_date'] < $endDate) {
                        $updateRows[$shopId]['end_date'] = $endDate;
                    }
                    if (isset($updateRows[$shopId])) {
                        $updateRows[$shopId]['id'] = $history['id'];
                        break;
                    }
                }
            }
            if (!$exists || !isset($updateRows[$shopId])) {
                $insertRows[] = [
                    'shop_id' => $shopId,
                    'begin_date' => $beginDate,
                    'end_date' => $endDate,
                    'status' => SyncTask::STATUS_PENDING,
                    'priority' => $this->priority,
                ];
            }
        }

        $insertRows && $cmd->batchInsert('{{%g_sync_task}}', array_keys($insertRows[0]), $insertRows)->execute();

        if ($updateRows) {
            foreach ($updateRows as $column) {
                $id = $column['id'];
                unset($column['id']);
                $cmd->update('{{%g_sync_task}}', $column, ['id' => $id])->execute();
            }
        }

        return true;
    }

    public function attributeLabels()
    {
        return [
            'shop_ids' => '店铺',
            'begin_date' => '开始时间',
            'end_date' => '结束时间',
            'priority' => '优先级',
        ];
    }

}
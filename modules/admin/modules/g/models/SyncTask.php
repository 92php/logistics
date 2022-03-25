<?php

namespace app\modules\admin\modules\g\models;

/**
 * This is the model class for table "{{%g_sync_task}}".
 *
 * @property int $id
 * @property int $shop_id 店铺
 * @property int $begin_date 开始日期
 * @property int $end_date 结束日期
 * @property int $priority 优先级
 * @property int $status 状态
 */
class SyncTask extends \yii\db\ActiveRecord
{

    /**
     * 状态选项
     */
    const STATUS_PENDING = 0;
    const STATUS_WORKING = 1;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%g_sync_task}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['shop_id', 'begin_date', 'end_date'], 'required'],
            [['shop_id', 'priority', 'status'], 'integer'],
            [['begin_date', 'end_date', 'start_datetime'], 'date', 'format' => 'php:Y-m-d'],
            ['status', 'default', 'value' => self::STATUS_PENDING],
            ['status', 'in', 'range' => array_keys(self::statusOptions())],
            ['priority', 'default', 'value' => 10],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => '编号',
            'shop_id' => '店铺',
            'shop.name' => '店铺',
            'begin_date' => '开始日期',
            'end_date' => '结束日期',
            'priority' => '优先级',
            'start_datetime' => '启动时间',
            'status' => '状态',
        ];
    }

    /**
     * 店铺
     *
     * @return \yii\db\ActiveQuery
     */
    public function getShop()
    {
        return $this->hasOne(Shop::class, ['id' => 'shop_id']);
    }

    /**
     * 状态选项
     *
     * @return array
     */
    public static function statusOptions()
    {
        return [
            self::STATUS_PENDING => "待处理",
            self::STATUS_WORKING => "处理中",
        ];
    }

    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            if ($insert) {
                $this->status = self::STATUS_PENDING;
            }

            return true;
        } else {
            return false;
        }
    }

}

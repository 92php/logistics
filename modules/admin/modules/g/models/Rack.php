<?php

namespace app\modules\admin\modules\g\models;

use yadjet\helpers\IsHelper;
use Yii;

/**
 * This is the model class for table "{{%g_rack}}".
 *
 * @property int $id
 * @property int $warehouse_id 仓库
 * @property int $block 仓库分区
 * @property string $number 货架编号
 * @property int $priority 拣货权重
 * @property string|null $remark 备注
 * @property int $created_at 添加时间
 * @property int $created_by 添加人
 * @property int $updated_at 更新时间
 * @property int $updated_by 更新人
 */
class Rack extends \yii\db\ActiveRecord
{

    /**
     * 分区选项
     */
    const BLOCK_PICKING_AREA = 0; // 拣货区
    const BLOCK_INFERIOR_AREA = 1; // 次品区

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%g_rack}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['warehouse_id', 'number'], 'required'],
            [['warehouse_id', 'block', 'priority', 'created_at', 'created_by', 'updated_at', 'updated_by'], 'integer'],
            [['remark'], 'string'],
            [['number', 'remark'], 'trim'],
            [['number'], 'string', 'max' => 255],
            ['block', 'default', 'value' => self::BLOCK_PICKING_AREA],
            ['block', 'in', 'range' => array_keys(self::blockOptions())],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'id',
            'warehouse_id' => '仓库',
            'block' => '仓库分区',
            'number' => '货架编号',
            'priority' => '拣货权重',
            'remark' => '备注',
            'created_at' => '添加时间',
            'created_by' => '添加人',
            'updated_at' => '修改时间',
            'updated_by' => '修改人',
        ];
    }

    /**
     * 分区选项
     *
     * @return array
     */
    public static function blockOptions()
    {
        return [
            self::BLOCK_PICKING_AREA => '拣货区',
            self::BLOCK_INFERIOR_AREA => '次品区'
        ];
    }

    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            $userId = IsHelper::cli() ? 0 : Yii::$app->getUser()->getId();
            if ($insert) {
                $this->created_at = $this->updated_at = time();
                $this->created_by = $this->updated_by = $userId;
            } else {
                $this->updated_at = time();
                $this->updated_by = $userId;
            }

            return true;
        } else {
            return false;
        }
    }

}

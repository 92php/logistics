<?php

namespace app\modules\admin\modules\wuliu\models;

use Yii;

/**
 * This is the model class for table "{{%wuliu_company_line_route}}".
 *
 * @property int $id
 * @property int $line_id 所属线路
 * @property int $step 步骤
 * @property string $event 事件
 * @property string $detection_keyword 判断依据
 * @property int $estimate_days 预计天数
 * @property int $package_status 包裹状态
 * @property int $enabled 激活
 * @property int|null $created_at 创建时间
 * @property int|null $created_by 创建人
 * @property int|null $updated_at 修改时间
 * @property int|null $updated_by 修改人
 */
class CompanyLineRoute extends \yii\db\ActiveRecord
{

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%wuliu_company_line_route}}';
    }

    public function transactions()
    {
        return [
            self::SCENARIO_DEFAULT => self::OP_ALL,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['line_id', 'event', 'detection_keyword'], 'required'],
            [['event', 'detection_keyword'], 'trim'],
            [['line_id', 'step', 'estimate_days', 'package_status', 'enabled'], 'integer'],
            ['estimate_days', 'default', 'value' => 0],
            ['package_status', 'default', 'value' => Package::STATUS_PENDING],
            [['event'], 'string', 'max' => 30],
            [['detection_keyword'], 'string', 'max' => 200],
//            [['step'], 'unique', 'targetClass' => static::class, 'targetAttribute' => ['line_id', 'step'], 'message' => '{attribute}{value}同一条线路中步骤不能相同。'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => '编号',
            'line_id' => '所属线路',
            'step' => '步骤',
            'event' => '事件',
            'detection_keyword' => '判断依据',
            'estimate_days' => '预计天数',
            'package_status' => '包裹状态',
            'enabled' => '激活',
            'created_at' => '创建时间',
            'created_by' => '创建人',
            'updated_at' => '修改时间',
            'updated_by' => '修改人',
        ];
    }

    /**
     * 所属线路
     *
     * @return \yii\db\ActiveQuery
     */
    public function getLine()
    {
        return $this->hasOne(CompanyLine::class, ['id' => 'line_id']);
    }

    /**
     * 获取路线
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCompanyLine()
    {
        return $this->hasOne(CompanyLine::class, ['id' => 'line_id']);
    }

    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            if ($insert) {
                $this->created_at = $this->updated_at = time();
                $this->created_by = $this->updated_by = Yii::$app->getUser()->getId();
            } else {
                $this->updated_at = time();
                $this->updated_by = Yii::$app->getUser()->getId();
            }

            return true;
        } else {
            return false;
        }
    }

    public function afterDelete()
    {
        parent::afterDelete();
        PackageRoute::deleteAll(['route_line_id' => $this->id]);
    }

}

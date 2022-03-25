<?php

namespace app\modules\admin\modules\wuliu\models;

use app\modules\api\models\Constant;
use Yii;

/**
 * This is the model class for table "{{%wuliu_freight_template_fee}}".
 *
 * @property int $id
 * @property int $template_id 模板
 * @property int $line_id 物流线路
 * @property int $min_weight 最小重量
 * @property int $max_weight 最大重量
 * @property int $first_weight 首重
 * @property float $first_fee 首重费用
 * @property int $continued_weight 续重
 * @property float $continued_fee 续重费用
 * @property float $fixed_fee 固定费用
 * @property float $base_fee 挂号费
 * @property float $freight_fee_rate 运费基数折扣率
 * @property float $base_fee_rate 挂号费折扣率
 * @property int $enabled 激活
 * @property string|null $remark 备注
 * @property int|null $created_at 创建时间
 * @property int|null $created_by 创建人
 * @property int|null $updated_at 修改时间
 * @property int|null $updated_by 修改人
 */
class FreightTemplateFee extends \yii\db\ActiveRecord
{

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%wuliu_freight_template_fee}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['template_id', 'line_id', 'min_weight', 'max_weight', 'base_fee'], 'required'],
            [['template_id', 'line_id'], 'integer'],
            [['enabled'], 'boolean'],
            [['min_weight', 'max_weight'], 'integer', 'min' => 1],
            [['first_weight', 'continued_weight'], 'integer', 'min' => 0],
            [['first_weight', 'continued_weight', 'first_fee', 'continued_fee', 'fixed_fee', 'freight_fee_rate', 'base_fee_rate'], 'default', 'value' => 0],
            ['enabled', 'default', 'value' => Constant::BOOLEAN_TRUE],
            [['first_fee', 'continued_fee', 'base_fee', 'fixed_fee', 'freight_fee_rate', 'base_fee_rate'], 'number', 'min' => 0],
            [['remark'], 'string'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'template_id' => '模板',
            'line_id' => '物流线路',
            'min_weight' => '最小重量',
            'max_weight' => '最大重量',
            'first_weight' => '首重',
            'first_fee' => '首重费用',
            'continued_weight' => '续重',
            'continued_fee' => '续重费用',
            'fixed_fee' => '固定费用',
            'base_fee' => '挂号费',
            'freight_fee_rate' => '运费基数折扣率',
            'base_fee_rate' => '挂号费折扣率',
            'enabled' => '激活',
            'remark' => '备注',
            'created_at' => '创建时间',
            'created_by' => '创建人',
            'updated_at' => '修改时间',
            'updated_by' => '修改人',
        ];
    }

    /**
     * 所属模板
     *
     * @return \yii\db\ActiveQuery
     */
    public function getTemplate()
    {
        return $this->hasOne(FreightTemplate::class, ['id' => 'template_id']);
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

}

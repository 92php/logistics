<?php

namespace app\modules\admin\modules\wuliu\models;

use app\modules\api\models\Constant;
use Yii;
use yii\db\Query;

/**
 * This is the model class for table "{{%wuliu_freight_template}}".
 *
 * @property int $id
 * @property int $company_id 物流公司
 * @property string $name 名称
 * @property int $fee_mode 计费方式
 * @property int $enabled 激活
 * @property string|null $remark 备注
 * @property int|null $created_at 创建时间
 * @property int|null $created_by 创建人
 * @property int|null $updated_at 修改时间
 * @property int|null $updated_by 修改人
 */
class FreightTemplate extends \yii\db\ActiveRecord
{

    /**
     * 计费模式
     */
    const FEE_MODE_VOLUME = 0;
    const FEE_MODE_FIRST_WEIGHT = 1; // 首重续费运费
    const FEE_MODE_IDENTICAL_WEIGHT = 2; // 同重量运费

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%wuliu_freight_template}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['company_id', 'name', 'fee_mode'], 'required'],
            ['fee_mode', 'in', 'range' => array_keys(self::feeModeOptions())],
            [['company_id', 'fee_mode'], 'integer'],
            [['enabled'], 'boolean'],
            ['enabled', 'default', 'value' => Constant::BOOLEAN_TRUE],
            ['fee_mode', 'default', 'value' => 1],
            [['remark'], 'string'],
            [['name'], 'string', 'max' => 30],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'company_id' => '物流公司',
            'name' => '名称',
            'fee_mode' => '计费方式',
            'enabled' => '激活',
            'remark' => '备注',
            'created_at' => '创建时间',
            'created_by' => '创建人',
            'updated_at' => '修改时间',
            'updated_by' => '修改人',
        ];
    }

    /**
     * 计费方式
     *
     * @return array
     */
    public static function feeModeOptions()
    {
        return [
            self::FEE_MODE_VOLUME => '按体积计费',
            self::FEE_MODE_FIRST_WEIGHT => '按首重重量计费',
            self::FEE_MODE_IDENTICAL_WEIGHT => '按同重量段计费'
        ];
    }

    /**
     * 模板列表
     *
     * @return array
     */
    public static function map()
    {
        return (new Query())
            ->select(['[[name]]'])
            ->from('{{%wuliu_freight_template}}')
            ->indexBy('id')
            ->column();
    }

    /**
     * 计费详情
     *
     * @return \yii\db\ActiveQuery
     */
    public function getTemplateFees()
    {
        return $this->hasMany(FreightTemplateFee::class, ['template_id' => 'id']);
    }

    /**
     * 所属公司
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCompany()
    {
        return $this->hasOne(Company::class, ['id' => 'company_id']);
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
        FreightTemplateFee::deleteAll(['template_id' => $this->id]);
    }

}

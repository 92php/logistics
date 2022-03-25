<?php

namespace app\modules\admin\modules\wuliu\models;

use app\modules\api\models\Constant;
use yadjet\helpers\IsHelper;
use Yii;
use yii\db\Query;

/**
 * This is the model class for table "{{%wuliu_company}}".
 *
 * @property int $id
 * @property string $code 代码
 * @property string $name 公司名称
 * @property string $website_url 网站
 * @property string $linkman 联系人
 * @property string $mobile_phone 手机号码
 * @property int $enabled 激活
 * @property string|null $remark 备注
 * @property int|null $created_at 创建时间
 * @property int|null $created_by 创建人
 * @property int|null $updated_at 修改时间
 * @property int|null $updated_by 修改人
 */
class Company extends \yii\db\ActiveRecord
{

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%wuliu_company}}';
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
            [['code', 'name', 'website_url', 'linkman', 'mobile_phone'], 'required'],
            [['code', 'name', 'website_url', 'linkman', 'mobile_phone', 'remark'], 'trim'],
            [['remark'], 'string'],
            ['code', 'string', 'max' => 20],
            ['code', 'filter', 'filter' => 'strtolower'],
            [['name', 'mobile_phone'], 'string', 'max' => 30],
            ['website_url', 'url'],
            ['enabled', 'default', 'value' => Constant::BOOLEAN_TRUE],
            [['website_url'], 'string', 'max' => 100],
            [['linkman'], 'string', 'max' => 20],
            ['code', 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => '编号',
            'code' => '代码',
            'name' => '公司名称',
            'website_url' => '网站',
            'linkman' => '联系人',
            'mobile_phone' => '手机号码',
            'enabled' => '激活',
            'remark' => '备注',
            'created_at' => '创建时间',
            'created_by' => '创建人',
            'updated_at' => '修改时间',
            'updated_by' => '修改人',
        ];
    }

    /**
     * 物流公司列表
     *
     * @return array
     */
    public static function map()
    {
        return (new Query())
            ->select(['[[name]]'])
            ->from('{{%wuliu_company}}')
            ->indexBy('id')
            ->column();
    }

    /**
     * 物流线路
     *
     * @return \yii\db\ActiveQuery
     */
    public function getLines()
    {
        return $this->hasMany(CompanyLine::class, ['company_id' => 'id']);
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

    public function afterDelete()
    {
        parent::afterDelete();
        CompanyLine::deleteAll(['company_id' => $this->id]);
    }

}

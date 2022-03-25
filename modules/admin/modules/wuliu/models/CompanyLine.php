<?php

namespace app\modules\admin\modules\wuliu\models;

use app\modules\admin\modules\g\models\Country;
use app\modules\api\models\Constant;
use yadjet\helpers\IsHelper;
use Yii;
use yii\db\Query;

/**
 * This is the model class for table "{{%wuliu_company_line}}".
 *
 * @property int $id
 * @property int $company_id 所属公司
 * @property int $country_id 国家
 * @property string $name_prefix 线路名称前缀
 * @property string $name 线路名称
 * @property int $estimate_days 预计天数
 * @property int $enabled 激活
 * @property string|null $remark 备注
 * @property int|null $created_at 创建时间
 * @property int|null $created_by 创建人
 * @property int|null $updated_at 修改时间
 * @property int|null $updated_by 修改人
 */
class CompanyLine extends \yii\db\ActiveRecord
{

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%wuliu_company_line}}';
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
            [['company_id', 'name', 'country_id'], 'required'],
            [['name_prefix', 'name', 'remark'], 'trim'],
            [['company_id', 'estimate_days', 'country_id'], 'integer'],
            ['estimate_days', 'default', 'value' => 0],
            ['enabled', 'default', 'value' => Constant::BOOLEAN_TRUE],
            [['remark'], 'string'],
            [['name_prefix'], 'string', 'max' => 30],
            [['name'], 'string', 'max' => 100],
            ['country_id', 'exist', 'targetClass' => Country::class, 'targetAttribute' => ['country_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => '编号',
            'company_id' => '所属公司',
            'country_id' => '国家',
            'company.name' => '所属公司',
            'name_prefix' => '线路名称前缀',
            'name' => '线路名称',
            'estimate_days' => '预计天数',
            'enabled' => '激活',
            'remark' => '备注',
            'created_at' => '创建时间',
            'created_by' => '创建人',
            'updated_at' => '修改时间',
            'updated_by' => '修改人',
        ];
    }

    /**
     * 物流公司线路列表
     *
     * @return array
     */
    public static function map()
    {
        $items = [];
        $rawItems = (new Query())
            ->select(['t.id', 't.name', 'c.name AS company_name'])
            ->from('{{%wuliu_company_line}} t')
            ->leftJoin('{{%wuliu_company}} c', '[[t.company_id]] = [[c.id]]')
            ->orderBy(['t.company_id' => SORT_ASC])
            ->all();
        foreach ($rawItems as $item) {
            $items[$item['id']] = "{$item['company_name']} - {$item['name']}";
        }

        return $items;
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

    /**
     * 线路路由
     *
     * @return \yii\db\ActiveQuery
     */
    public function getRoutes()
    {
        return $this->hasMany(CompanyLineRoute::class, ['line_id' => 'id'])
            ->orderBy(['step' => SORT_ASC]);
    }

    public function getCountry()
    {
        return $this->hasOne(Country::class, ['id' => 'country_id']);
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
        CompanyLineRoute::deleteAll(['line_id' => $this->id]);
    }

}

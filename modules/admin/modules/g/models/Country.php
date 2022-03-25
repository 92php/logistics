<?php

namespace app\modules\admin\modules\g\models;

use app\models\Constant;
use app\models\Option;
use yadjet\helpers\IsHelper;
use Yii;
use yii\db\Query;

/**
 * This is the model class for table "{{%g_country}}".
 *
 * @property int $id
 * @property int $region_id 地区
 * @property string $abbreviation 国家简称
 * @property string $chinese_name 中文名称
 * @property string $english_name 英文名称
 * @property bool $enabled 激活
 * @property int|null $created_at 创建时间
 * @property int|null $created_by 创建人
 * @property int|null $updated_at 修改时间
 * @property int|null $updated_by 修改人
 */
class Country extends \yii\db\ActiveRecord
{

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%g_country}}';
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
            [['region_id', 'abbreviation', 'chinese_name', 'english_name'], 'required'],
            ['abbreviation', 'filter', 'filter' => 'strtoupper'],
            [['abbreviation', 'chinese_name', 'english_name'], 'trim'],
            [['region_id'], 'integer'],
            ['region_id', 'in', 'range' => array_keys(Option::regions())],
            [['abbreviation'], 'string', 'max' => 6],
            [['chinese_name', 'english_name'], 'string', 'max' => 30],
            [['abbreviation'], 'unique'],
            [['chinese_name'], 'unique'],
            [['english_name'], 'unique'],
            ['enabled', 'default', 'value' => Constant::BOOLEAN_TRUE],
            ['enabled', 'boolean'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => '编号',
            'region_id' => '地区',
            'abbreviation' => '国家简称',
            'chinese_name' => '中文名称',
            'english_name' => '英文名称',
            'enabled' => '激活',
            'created_at' => '创建时间',
            'created_by' => '创建人',
            'updated_at' => '修改时间',
            'updated_by' => '修改人',
        ];
    }

    /**
     * 国家列表
     *
     * @param bool $all
     * @return array
     */
    public static function map($all = false)
    {
        $where = [];
        if (!$all) {
            $where['enabled'] = Constant::BOOLEAN_TRUE;
        }

        return (new Query())
            ->select(['[[chinese_name]]'])
            ->from('{{%g_country}}')
            ->where($where)
            ->indexBy('id')
            ->column();
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

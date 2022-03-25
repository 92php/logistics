<?php

namespace app\modules\admin\modules\g\models;

use Yii;

/**
 * This is the model class for table "{{%g_customs_declaration_document}}".
 *
 * @property int $id
 * @property string|null $code 海关编码
 * @property string $chinese_name 商品中文名称
 * @property string $english_name 商品英文名称
 * @property int $weight 申报重量
 * @property float $amount 申报金额
 * @property int $danger_level 危险等级
 * @property int $default 默认
 * @property int $enabled 激活
 * @property int $created_at 创建时间
 * @property int $created_by 创建人
 * @property int $updated_at 修改时间
 * @property int $updated_by 修改人
 */
class CustomsDeclarationDocument extends \yii\db\ActiveRecord
{

    /**
     * 危险等级
     */
    const DANGER_LEVEL_NONE = 0;
    const DANGER_LEVEL_HAN_DIAN = 1; // 含电(内电)
    const DANGER_LEVEL_CHUN_DIAN = 2; // 纯电
    const DANGER_LEVEL_YE_TI = 3; // 液体
    const DANGER_LEVEL_FEN_MO = 4; // 粉末
    const DANGER_LEVEL_GAO_TI = 5; // 膏体
    const DANGER_LEVEL_DAI_CI = 6; // 带磁
    const DANGER_LEVEL_HAN_FEI_YE_TI_HUA_ZHUANG_PIN = 7; // 含非液体化妆品

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%g_customs_declaration_document}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['chinese_name', 'english_name'], 'required'],
            [['weight', 'danger_level', 'default', 'enabled', 'created_at', 'created_by', 'updated_at', 'updated_by'], 'integer'],
            [['amount'], 'number'],
            [['code'], 'string', 'max' => 30],
            [['chinese_name'], 'string', 'max' => 200],
            [['english_name'], 'string', 'max' => 300],
            ['danger_level', 'default', 'value' => self::DANGER_LEVEL_NONE],
            ['danger_level', 'in', 'range' => array_keys(self::dangerLevelOptions())],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'code' => '海关编码',
            'chinese_name' => '商品中文名称',
            'english_name' => '商品英文名称',
            'weight' => '申报重量',
            'amount' => '申报金额',
            'danger_level' => '危险等级',
            'default' => '默认',
            'enabled' => '激活',
            'created_at' => '创建时间',
            'created_by' => '创建人',
            'updated_at' => '修改时间',
            'updated_by' => '修改人',
        ];
    }

    /**
     * 危险等级选项
     *
     * @return array
     */
    public static function dangerLevelOptions()
    {
        return [
            self::DANGER_LEVEL_NONE => '无',
            self::DANGER_LEVEL_HAN_DIAN => '含电(内电)',
            self::DANGER_LEVEL_CHUN_DIAN => '纯电',
            self::DANGER_LEVEL_YE_TI => '液体',
            self::DANGER_LEVEL_FEN_MO => '粉末',
            self::DANGER_LEVEL_GAO_TI => '膏体',
            self::DANGER_LEVEL_DAI_CI => '带磁',
            self::DANGER_LEVEL_HAN_FEI_YE_TI_HUA_ZHUANG_PIN => '含非液体化妆品',
        ];
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

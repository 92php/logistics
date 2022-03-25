<?php

namespace app\modules\admin\modules\g\models;

use app\helpers\Config;
use app\models\Constant;
use app\models\Option;
use app\modules\admin\modules\g\extensions\Formatter;
use Yii;
use yii\db\Query;

/**
 * This is the model class for table "{{%g_third_party_authentication}}".
 *
 * @property int $id
 * @property int $platform_id 所属平台
 * @property string $name 名称
 * @property string|null $authentication_config 访问配置
 * @property int $enabled 激活
 * @property string|null $remark 备注
 * @property int $created_at 添加时间
 * @property int $created_by 添加人
 * @property int $updated_at 更新时间
 * @property int $updated_by 更新人
 */
class ThirdPartyAuthentication extends \yii\db\ActiveRecord
{

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%g_third_party_authentication}}';
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
            [['platform_id', 'name'], 'required'],
            [['name', 'remark'], 'trim'],
            [['platform_id', 'created_at', 'created_by', 'updated_at', 'updated_by'], 'integer'],
            ['platform_id', 'in', 'range' => array_keys(Option::thirdPartyPlatforms())],
            ['enabled', 'boolean'],
            ['enabled', 'default', 'value' => Constant::BOOLEAN_TRUE],
            [['authentication_config'], 'safe'],
            ['authentication_config', function ($attribute, $params) {
                $rawConfigurations = $this->$attribute;
                if (is_array($rawConfigurations)) {
                    $configurationPatterns = Config::get("platform.{$this->platform_id}");
                    if ($configurationPatterns === null) {
                        $this->addError($attribute, '配置参数错误。');
                    } else {
                        $configurations = [];
                        $ok = true;
                        $labels = $this->attributeLabels();
                        foreach ($configurationPatterns as $k => $patterns) {
                            foreach ($patterns as $kk => $pattern) {
                                $isRequired = isset($pattern['required']) && $pattern['required'];
                                if ($isRequired && (!isset($rawConfigurations[$k][$kk]) || $rawConfigurations[$k][$kk] === '')) {
                                    $ok = false;
                                    $text = isset($labels[$attribute]) ? $labels[$attribute] : '';
                                    $text .= ' ' . $pattern['label'];
                                    $this->addError($attribute, "{$text} 不能为空。");
                                    break;
                                }

                                $value = isset($rawConfigurations[$k][$kk]) ? $rawConfigurations[$k][$kk] : null;
                                switch ($pattern['valueType']) {
                                    case 'boolean':
                                        $value = boolval($value);
                                        break;

                                    case 'integer':
                                        $value = intval($value);
                                        break;

                                    default:
                                        $value = strval($value);
                                        if ($pattern['valueType'] == 'string' && $isRequired) {
                                            $n = strlen($value);
                                            if (!isset($pattern['min'])) {
                                                $pattern['min'] = $isRequired ? 1 : 0;
                                            }
                                            if (!isset($pattern['max'])) {
                                                $pattern['max'] = 1024;
                                            }
                                            if ($n < $pattern['min'] || $n > $pattern['max']) {
                                                $ok = false;
                                                $text = isset($labels[$attribute]) ? $labels[$attribute] : '';
                                                $text .= ' ' . $pattern['label'];
                                                $this->addError($attribute, "$text 字段长度须在 {$pattern['min']} - {$pattern['max']} 之间。");
                                                break;
                                            }
                                        }
                                        break;
                                }
                                $configurations[$k][$kk] = $value;
                            }
                        }
                        if ($ok) {
                            $this->$attribute = $configurations;
                        }
                    }
                } else {
                    $this->addError($attribute, '配置参数错误。');
                }
            }],
            [['remark'], 'string'],
            [['name'], 'string', 'max' => 100],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => '编号',
            'platform_id' => '第三方平台',
            'name' => '名称',
            'authentication_config' => '访问配置',
            'enabled' => '激活',
            'remark' => '备注',
            'created_at' => '添加时间',
            'created_by' => '添加人',
            'updated_at' => '更新时间',
            'updated_by' => '更新人',
        ];
    }

    /**
     * 店铺列表
     *
     * @return array
     * @throws \yii\db\Exception
     */
    public static function map()
    {
        $items = [];
        /* @var $formatter Formatter */
        $formatter = Yii::$app->getFormatter();
        $rows = Yii::$app->getDb()->createCommand('SELECT [[id]], [[platform_id]], [[name]] FROM {{%g_third_party_authentication}} ORDER BY [[platform_id]] ASC')->queryAll();
        foreach ($rows as $row) {
            $items[$row['id']] = sprintf("[ %s ] %s", $formatter->asThirdPartyPlatform($row['platform_id']), $row['name']);
        }

        return $items;
    }

    /**
     * 获取配置信息
     *
     * @return array
     */
    public function getConfigurations()
    {
        $values = [];
        $configurationPatterns = Config::get("platform.$this->platform_id");
        if ($configurationPatterns) {
            $parseValue = function ($value, $type = 'string') {
                switch ($type) {
                    case 'integer':
                        $value = intval($value);
                        break;

                    case 'boolean':
                        $value = boolval($value);
                        break;

                    default:
                        $value = strval($value);
                        break;
                }

                return $value;
            };
            // 设置默认值
            foreach ($configurationPatterns as $k => $patterns) {
                foreach ($patterns as $kk => $pattern) {
                    $values[$k][$kk] = $parseValue(null, $pattern['valueType']);
                }
            }
            foreach ($values as $k => $v) {
                foreach ($v as $kk => $vv) {
                    isset($this->authentication_config[$k][$kk]) && $values[$k][$kk] = $parseValue($this->authentication_config[$k][$kk], $configurationPatterns[$k][$kk]['valueType']);
                }
            }
        }

        return $values;
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

    /**
     * @throws \yii\db\Exception
     */
    public function afterDelete()
    {
        parent::afterDelete();
        Yii::$app->getDb()
            ->createCommand()
            ->update(
                Shop::tableName(),
                ['third_party_authentication_id' => 0],
                ['third_party_authentication_id' => $this->id]
            )
            ->execute();
    }

}

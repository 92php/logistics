<?php

namespace app\modules\admin\modules\g\models;

use app\models\Constant;
use Yii;

/**
 * This is the model class for table "{{%g_tag}}".
 *
 * @property int $id
 * @property int $type 类型
 * @property int $parent_id 上级标签
 * @property string $name 标签名称
 * @property int $ordering 排序
 * @property int $enabled 激活
 * @property int $created_at 创建时间
 * @property int $created_by 创建人
 * @property int|null $updated_at 修改时间
 * @property int|null $updated_by 修改人
 */
class Tag extends \yii\db\ActiveRecord
{

    /**
     * 类型
     */
    const TYPE_NONE = 0; // 未分类
    const TYPE_CUSTOMS_DECLARATION_ATTRIBUTES = 1; // 报关属性

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%g_tag}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['type', 'parent_id', 'ordering', 'created_at', 'created_by', 'updated_at', 'updated_by'], 'integer'],
            [['name'], 'required'],
            [['name'], 'trim'],
            [['name'], 'string', 'max' => 30],
            ['type', 'default', 'value' => self::TYPE_NONE],
            ['type', 'in', 'range' => array_keys(self::typeOptions())],
            ['enabled', 'boolean'],
            ['enabled', 'default', 'value' => Constant::BOOLEAN_TRUE],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'type' => '类型',
            'parent_id' => '上级标签',
            'name' => '标签名称',
            'ordering' => '排序',
            'enabled' => '激活',
            'created_at' => '创建时间',
            'created_by' => '创建人',
            'updated_at' => '修改时间',
            'updated_by' => '修改人',
        ];
    }

    /**
     * 类型选项
     *
     * @return array
     */
    public static function typeOptions()
    {
        return [
            self::TYPE_NONE => '未分类',
            self::TYPE_CUSTOMS_DECLARATION_ATTRIBUTES => '报关属性',
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

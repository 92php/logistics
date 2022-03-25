<?php

namespace app\modules\admin\modules\hub\modules\shopify\models;

use app\models\Constant;
use app\modules\admin\modules\hub\models\Project;
use Yii;
use yii\db\Query;

/**
 * This is the model class for table "{{%hub_shop}}".
 *
 * @property int $id
 * @property int $project_id 所属项目
 * @property string $key Key
 * @property string $name 名称
 * @property string $url 访问地址
 * @property string $api_key API Key
 * @property string $api_password API 密码
 * @property string $api_shared_secret API Shared Secret
 * @property string $webhooks_shared_secret Webhooks Shared Secret
 * @property float $fixed_fee 固定费用
 * @property string|null $remark 备注
 * @property int $enabled 激活
 * @property int $created_at 添加时间
 * @property int $created_by 添加人
 * @property int $updated_at 更新时间
 * @property int $updated_by 更新人
 */
class Shop extends \yii\db\ActiveRecord
{

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%hub_shop}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name', 'key', 'url', 'api_key', 'api_password', 'api_shared_secret', 'webhooks_shared_secret', 'enabled'], 'required'],
            [['project_id', 'created_at', 'created_by', 'updated_at', 'updated_by'], 'integer'],
            ['project_id', 'default', 'value' => 0],
            [['enabled'], 'default', 'value' => Constant::BOOLEAN_TRUE],
            [['enabled'], 'boolean'],
            [['fixed_fee'], 'number'],
            [['key', 'name', 'url', 'api_key', 'api_password', 'api_shared_secret', 'webhooks_shared_secret'], 'trim'],
            ['key', 'match', 'pattern' => '/^[a-z0-9]+[a-z0-9-]+[a-z0-9]$/'],
            [['key'], 'string', 'max' => 30],
            [['name'], 'string', 'max' => 60],
            [['webhooks_shared_secret'], 'string', 'max' => 70],
            [['url', 'api_key', 'api_password', 'api_shared_secret'], 'string', 'max' => 100],
            [['remark'], 'string', 'max' => 200],
            [['key'], 'unique'],
            [['url'], 'unique'],
            ['url', 'url', 'defaultScheme' => 'https'],
            [['api_key'], 'unique'],
            [['api_password'], 'unique'],
            [['api_shared_secret'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => '编号',
            'project_id' => '所属项目',
            'project.name' => '所属项目',
            'key' => 'Key',
            'name' => '名称',
            'url' => '访问地址',
            'api_key' => 'API Key',
            'api_password' => 'API 密码',
            'api_shared_secret' => 'API Shared Secret',
            'webhooks_shared_secret' => 'Webhooks Shared Secret',
            'fixed_fee' => '固定费用',
            'remark' => '备注',
            'enabled' => '激活',
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
     */
    public static function map()
    {
        return (new Query())
            ->select(['name'])
            ->from('{{%hub_shop}}')
            ->indexBy('id')
            ->column();
    }

    /**
     * 所属项目
     *
     * @return \yii\db\ActiveQuery
     */
    public function getProject()
    {
        return $this->hasOne(Project::class, ['id' => 'project_id']);
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

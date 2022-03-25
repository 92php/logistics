<?php

namespace app\modules\admin\modules\g\models;

use app\models\Constant;
use app\models\Option;
use app\modules\admin\modules\g\extensions\Formatter;
use Yii;

/**
 * This is the model class for table "{{%g_shop}}".
 *
 * @property int $id
 * @property int $organization_id 所属组织
 * @property int $platform_id 所属平台
 * @property string $name 店铺名称
 * @property string $url 访问地址
 * @property int $product_type 商品类型
 * @property int $third_party_authentication_id 第三方平台访问配置
 * @property string|null $third_party_sign 第三方标记
 * @property int $enabled 激活
 * @property string|null $remark 备注
 * @property int $created_at 添加时间
 * @property int $created_by 添加人
 * @property int $updated_at 更新时间
 * @property int $updated_by 更新人
 */
class Shop extends \yii\db\ActiveRecord
{

    /**
     * 商品类型
     */
    const PRODUCT_TYPE_UNKNOWN = 0; // 未知
    const PRODUCT_TYPE_GENERAL = 1; // 普货
    const PRODUCT_TYPE_CUSTOMIZED = 2; // 定制
    const PRODUCT_TYPE_GENERAL_AND_CUSTOMIZED = 3; // 普货、定制

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%g_shop}}';
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
            [['organization_id', 'platform_id', 'name', 'url', 'product_type'], 'required'],
            [['name', 'url', 'third_party_sign', 'remark'], 'trim'],
            [['product_type', 'organization_id', 'platform_id', 'third_party_authentication_id', 'created_at', 'created_by', 'updated_at', 'updated_by'], 'integer'],
            ['organization_id', 'in', 'range' => array_keys(Option::organizations())],
            ['platform_id', 'in', 'range' => array_keys(Option::platforms())],
            ['third_party_authentication_id', 'default', 'value' => 0],
            ['enabled', 'boolean'],
            ['enabled', 'default', 'value' => Constant::BOOLEAN_TRUE],
            [['remark'], 'string'],
            [['name'], 'string', 'max' => 100],
            ['url', 'url'],
            [['url', 'third_party_sign'], 'string', 'max' => 200],
            ['product_type', 'default', 'value' => self::PRODUCT_TYPE_GENERAL],
            ['product_type', 'in', 'range' => array_keys(self::productTypeOptions())],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => '编号',
            'organization_id' => '所属组织',
            'platform_id' => '所属平台',
            'name' => '店铺名称',
            'url' => '访问地址',
            'product_type' => '商品类型',
            'third_party_authentication_id' => '第三方认证配置',
            'thirdPartyAuthentication.name' => '第三方认证配置',
            'third_party_sign' => '第三方标记',
            'enabled' => '激活',
            'remark' => '备注',
            'created_at' => '添加时间',
            'created_by' => '添加人',
            'updated_at' => '更新时间',
            'updated_by' => '更新人',
        ];
    }

    /**
     * 列表
     *
     * @return array
     * @throws \yii\db\Exception
     */
    public static function map()
    {
        $items = [];
        $rawItems = Yii::$app->getDb()->createCommand('SELECT [[id]], [[organization_id]], [[platform_id]], [[name]], [[product_type]] FROM {{%g_shop}} ORDER BY [[organization_id]] ASC, [[platform_id]] ASC')->queryAll();
        /* @var $formatter Formatter */
        $formatter = Yii::$app->getFormatter();
        foreach ($rawItems as $rawItem) {
            $items[$rawItem['id']] = sprintf('%s【%s《%s》: %s】',
                $formatter->asOrganization($rawItem['organization_id']),
                $formatter->asPlatform($rawItem['platform_id']),
                $formatter->asProductType($rawItem['product_type']),
                $rawItem['name']
            );
        }

        return $items;
    }

    /**
     * 商品类型选项
     *
     * @return string[]
     */
    public static function productTypeOptions()
    {
        return [
            self::PRODUCT_TYPE_UNKNOWN => '未知',
            self::PRODUCT_TYPE_GENERAL => '普货',
            self::PRODUCT_TYPE_CUSTOMIZED => '定制',
            self::PRODUCT_TYPE_GENERAL_AND_CUSTOMIZED => '普货、定制',
        ];
    }

    /**
     * 第三方平台认证
     *
     * @return \yii\db\ActiveQuery
     */
    public function getThirdPartyAuthentication()
    {
        return $this->hasOne(ThirdPartyAuthentication::class, ['id' => 'third_party_authentication_id']);
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

<?php

namespace app\modules\admin\modules\g\models;

use app\modules\api\models\Constant;
use Yii;
use yii\db\ActiveQuery;

/**
 * This is the model class for table "{{%g_warehouse}}".
 *
 * @property int $id
 * @property string $name 仓库名称
 * @property string $address 地址
 * @property string $linkman 联系人
 * @property string $tel 电话
 * @property int $enabled 激活
 * @property string|null $remark 备注
 * @property int $created_at 创建时间
 * @property int $created_by 创建人
 * @property int $updated_at 修改时间
 * @property int $updated_by 修改人
 */
class Warehouse extends \yii\db\ActiveRecord
{

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%g_warehouse}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name', 'address', 'linkman', 'tel'], 'required'],
            [['name', 'address', 'linkman', 'tel', 'remark'], 'trim'],
            ['enabled', 'default', 'value' => Constant::BOOLEAN_TRUE],
            ['enabled', 'boolean'],
            [['created_at', 'created_by', 'updated_at', 'updated_by'], 'integer'],
            [['remark'], 'string'],
            [['name'], 'string', 'max' => 30],
            [['address'], 'string', 'max' => 100],
            [['linkman', 'tel'], 'string', 'max' => 10],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => '仓库名称',
            'address' => '地址',
            'linkman' => '联系人',
            'tel' => '电话',
            'enabled' => '激活',
            'remark' => '备注',
            'created_at' => '创建时间',
            'created_by' => '创建人',
            'updated_at' => '修改时间',
            'updated_by' => '修改人',
        ];
    }

    /**
     * 货架
     *
     * @return ActiveQuery
     */
    public function getRack()
    {
        return $this->hasMany(Rack::class, ['warehouse_id' => 'id']);
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

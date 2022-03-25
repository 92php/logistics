<?php

namespace app\modules\admin\modules\wuliu\models;

use app\jobs\DxmCookieJob;
use app\models\Option;
use app\modules\api\models\Constant;
use Yii;
use yii\db\Query;

/**
 * This is the model class for table "{{%wuliu_dxm_account}}".
 *
 * @property int $id
 * @property string $username 用户名
 * @property string $password 密码
 * @property int $company_id 物流公司
 * @property int $platform_id 所属平台
 * @property int $is_valid 是否有效
 * @property string|null $cookies cookies
 * @property string|null $remark 备注
 * @property int|null $created_at 创建时间
 * @property int|null $created_by 创建人
 * @property int|null $updated_at 修改时间
 * @property int|null $updated_by 修改人
 */
class DxmAccount extends \yii\db\ActiveRecord
{

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%wuliu_dxm_account}}';
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
            [['username', 'password'], 'required'],
            [['cookies'], 'required', 'when' => function ($model) {
                return $model->is_valid;
            }],
            [['username', 'password', 'remark', 'cookies'], 'trim'],
            [['company_id'], 'default', 'value' => 0],
            [['company_id', 'platform_id'], 'integer'],
            ['is_valid', 'default', 'value' => Constant::BOOLEAN_FALSE],
            [['is_valid'], 'boolean'],
            ['platform_id', 'default', 'value' => 0],
            ['platform_id', 'in', 'range' => array_keys(Option::platforms())],
            [['remark', 'cookies'], 'string'],
            [['username', 'password'], 'string', 'max' => 30],
            ['username', 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => '编号',
            'username' => '用户名',
            'password' => '密码',
            'company_id' => '物流公司',
            'platform_id' => '所属平台',
            'is_valid' => '是否有效',
            'cookies' => 'Cookies',
            'remark' => '备注',
            'created_at' => '创建时间',
            'created_by' => '创建人',
            'updated_at' => '修改时间',
            'updated_by' => '修改人',
        ];
    }

    /**
     * 物流公司
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCompany()
    {
        return $this->hasOne(Company::class, ['id' => 'company_id']);
    }

    /**
     * 店小秘账户列表
     *
     * @return array
     */
    public static function map()
    {
        return (new Query())
            ->select(['[[username]]'])
            ->from('{{%wuliu_dxm_account}}')
            ->indexBy('id')
            ->column();
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

    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);
        if (isset($changedAttributes['password']) || $insert) {
            Yii::$app->queue->push(new DxmCookieJob([
                'id' => $this->id,
            ]));
        }
    }

}

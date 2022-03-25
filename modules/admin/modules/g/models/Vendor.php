<?php

namespace app\modules\admin\modules\g\models;

use app\models\Constant;
use app\models\Member;
use app\modules\admin\modules\om\models\SkuVendor;
use yadjet\validators\MobilePhoneNumberValidator;
use yadjet\validators\PhoneNumberValidator;
use Yii;
use yii\db\Query;

/**
 * This is the model class for table "{{%g_vendor}}".
 *
 * @property int $id
 * @property string $name 供应商名称
 * @property string $address 地址
 * @property string|null $tel 联系电话
 * @property string $linkman 联系人
 * @property string $mobile_phone 手机号码
 * @property float|null $receipt_duration 接单时长
 * @property int $production 生产量/天
 * @property int|null $credibility 信誉度
 * @property int $enabled 激活
 * @property string|null $remark 备注
 * @property int $created_at 添加时间
 * @property int $created_by 添加人
 * @property int $updated_at 更新时间
 * @property int $updated_by 更新人
 */
class Vendor extends \yii\db\ActiveRecord
{

    /**
     * @var array 关联会员
     */
    public $member_ids;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%g_vendor}}';
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
            [['name', 'address', 'linkman', 'mobile_phone'], 'required'],
            [['name', 'address', 'linkman', 'tel', 'mobile_phone', 'remark'], 'trim'],
            [['receipt_duration'], 'number'],
            [['production', 'credibility', 'created_at', 'created_by', 'updated_at', 'updated_by'], 'integer'],
            ['enabled', 'boolean'],
            ['production', 'default', 'value' => 0],
            ['enabled', 'default', 'value' => Constant::BOOLEAN_TRUE],
            [['remark'], 'string'],
            [['name'], 'string', 'max' => 30],
            [['address'], 'string', 'max' => 100],
            [['tel'], 'string', 'max' => 13],
            [['tel'], PhoneNumberValidator::class],
            [['linkman'], 'string', 'max' => 10],
            [['mobile_phone'], 'string', 'max' => 11],
            ['mobile_phone', MobilePhoneNumberValidator::class],
            ['member_ids', 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => '编号',
            'name' => '供应商名称',
            'address' => '地址',
            'tel' => '联系电话',
            'linkman' => '联系人',
            'mobile_phone' => '手机号码',
            'receipt_duration' => '接单时长',
            'production' => '生产量/天',
            'credibility' => '信誉度',
            'enabled' => '激活',
            'remark' => '备注',
            'created_at' => '添加时间',
            'created_by' => '添加人',
            'updated_at' => '更新时间',
            'updated_by' => '更新人',
            'member_ids' => '关联会员'
        ];
    }

    /**
     * @return array 供应商列表
     */
    public static function map()
    {
        return (new Query())
            ->select(['name'])
            ->from(self::tableName())
            ->indexBy('id')
            ->column();
    }

    /**
     * 关联会员
     *
     * @return \yii\db\ActiveQuery
     * @throws \yii\base\InvalidConfigException
     */
    public function getMembers()
    {
        return $this->hasMany(Member::class, ['id' => 'member_id'])
            ->viaTable('{{%g_vendor_member}}', ['vendor_id' => 'id'])
            ->select(['id', 'username']);
    }

    public function afterFind()
    {
        parent::afterFind();
        if (!$this->getIsNewRecord()) {
            $this->member_ids = Yii::$app->getDb()->createCommand("SELECT [[member_id]] FROM {{%g_vendor_member}} WHERE [[vendor_id]] = :vendorId", [':vendorId' => $this->id])->queryColumn();
        }
    }

    /**
     * @param bool $insert
     * @return bool
     * @throws \yii\db\Exception
     */
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
            $db = Yii::$app->getDb();
            $existMemberIds = [];
            if (!$insert) {
                $existMemberIds = $db->createCommand("SELECT [[member_id]] FROM {{%g_vendor_member}} WHERE [[vendor_id]] = :vendorId", [':vendorId' => $this->id])->queryColumn();
            }
            $memberIds = is_array($this->member_ids) ? $this->member_ids : [];
            $insertMemberIds = array_diff($memberIds, $existMemberIds);
            $deleteMemberIds = array_diff($existMemberIds, $memberIds);
            if ($insertMemberIds || $deleteMemberIds) {
                $cmd = $db->createCommand();
                $rows = [];
                if ($insertMemberIds) {
                    foreach ($insertMemberIds as $memberId) {
                        $rows[] = [
                            'vendor_id' => $this->id,
                            'member_id' => $memberId,
                        ];
                    }
                }
                $rows && $cmd->batchInsert('{{%g_vendor_member}}', array_keys($rows[0]), $rows)->execute();
                $deleteMemberIds && $cmd->delete('{{%g_vendor_member}}', ['vendor_id' => $this->id, 'member_id' => $deleteMemberIds])->execute();
            }

            return true;
        } else {
            return false;
        }
    }

    public function afterDelete()
    {
        parent::afterDelete();
        SkuVendor::deleteAll(['vendor_id' => $this->id]);
    }

}

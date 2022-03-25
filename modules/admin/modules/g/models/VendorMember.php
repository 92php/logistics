<?php

namespace app\modules\admin\modules\g\models;

/**
 * This is the model class for table "{{%g_vendor_member}}".
 *
 * @property int $id
 * @property int $vendor_id 供应商
 * @property int $member_id 会员
 */
class VendorMember extends \yii\db\ActiveRecord
{

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%g_vendor_member}}';
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
            [['vendor_id', 'member_id'], 'required'],
            [['vendor_id', 'member_id'], 'integer'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'vendor_id' => '供应商',
            'member_id' => '会员',
        ];
    }

}

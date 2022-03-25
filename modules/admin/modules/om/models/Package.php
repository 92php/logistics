<?php

namespace app\modules\admin\modules\om\models;

use app\modules\api\models\Constant;
use Yii;

/**
 * This is the model class for table "{{%om_package}}".
 *
 * @property int $id
 * @property string $waybill_number 运单号
 * @property string $number 包裹号
 * @property string $title 包裹名称
 * @property string $logistics_company 物流公司
 * @property int|null $items_quantity 物品数量
 * @property int|null $remaining_items_quantity 待寄送物品数量
 * @property int $status 状态
 * @property int|null $created_at 创建时间
 * @property int|null $created_by 创建人
 * @property int|null $updated_at 修改时间
 * @property int|null $updated_by 修改人
 */
class Package extends \yii\db\ActiveRecord
{

    const STATUS_WAIT_DELIVER = 0; // 待发货
    const STATUS_DELIVER = 1; // 已发货

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%om_package}}';
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
            [['title', 'number'], 'required'],
            [['items_quantity', 'remaining_items_quantity', 'status'], 'integer'],
            [['items_quantity', 'remaining_items_quantity'], 'default', 'value' => 0],
            [['number'], 'string', 'length' => 19],
            [['waybill_number', 'number'], 'trim'],
            [['waybill_number'], 'string', 'max' => 20],
            [['number'], 'unique'],
            ['logistics_company', 'string', 'max' => 40],
            ['status', 'default', 'value' => Constant::BOOLEAN_FALSE]
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'waybill_number' => '运单号',
            'number' => '包裹号',
            'title' => '包裹名称',
            'logistics_company' => '物流公司',
            'items_quantity' => '物品数量',
            'remaining_items_quantity' => '待寄送物品数量',
            'status' => '状态',
            'created_at' => '创建时间',
            'created_by' => '创建人',
            'updated_at' => '修改时间',
            'updated_by' => '修改人',
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

    /**
     * 状态
     *
     * @return array
     */
    public function statusOptions()
    {
        return [
            self::STATUS_WAIT_DELIVER => '待发货',
            self::STATUS_DELIVER => '已发货',
        ];
    }

    /**
     * @throws \yii\db\Exception
     */
    public function afterDelete()
    {
        parent::afterDelete();
        // 订单所有包裹清除,状态返回为生产中
        $db = Yii::$app->getDb();
        $db->createCommand()->update('{{%om_order_item_route}}', ['package_id' => 0, 'current_node' => \app\modules\api\modules\om\models\OrderItemRoute::NODE_ALREADY_PRODUCED], ['package_id' => $this->id])->execute();
    }

}

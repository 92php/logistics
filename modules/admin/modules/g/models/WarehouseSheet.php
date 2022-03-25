<?php

namespace app\modules\admin\modules\g\models;

use Yii;
use yii\db\ActiveQuery;

/**
 * This is the model class for table "{{%g_warehouse_sheet}}".
 *
 * @property int $id
 * @property int $warehouse_id 仓库
 * @property string $number 流水单号
 * @property int|null $type 类型
 * @property int|null $method 出入库方式
 * @property int $change_datetime 商品出入库时间
 * @property string|null $remark 备注
 * @property int $created_at 添加人
 * @property int $created_by 添加时间
 */
class WarehouseSheet extends \yii\db\ActiveRecord
{

    /**
     *  类型
     */
    const TYPE_WAREHOUSING = 0; //入库
    const TYPE_WAREHOUSING_OUT = 1; //出库
    const TYPE_WAREHOUSING_ALLOCATION = 2; //调拨

    /**
     *  入库方式
     */
    const WAREHOUSING_MANUAL = 0; //手工入库
    const WAREHOUSING_RETURN = 1; //销售退货
    const WAREHOUSING_SURPLUS = 2; //盘盈入库
    const WAREHOUSING_PURCHASE = 3; //采购入库

    /**
     *  出库方式
     */
    const WAREHOUSING_OUT_MANUAL = 0; //手工出库
    const WAREHOUSING_OUT_RETURN = 1; //退货出库
    const WAREHOUSING_OUT_LOSS = 2; //盘亏出库
    const WAREHOUSING_OUT_SALE = 3; //销售入库

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%g_warehouse_sheet}}';
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
            [['change_datetime', 'type', 'method', 'warehouse_id'], 'required'],
            [['warehouse_id', 'type', 'method', 'created_at', 'created_by'], 'integer'],
            [['number', 'remark'], 'trim'],
            [['number'], 'string', 'max' => 30],
            [['number'], 'unique'],
            [['remark'], 'string'],
            ['change_datetime', 'date', 'format' => 'php:Y-m-d H:i:s'],
            ['type', 'in', 'range' => array_keys(self::TypeOptions())],
            ['method', 'in', 'range' => array_keys(self::warehousingOptions()), 'when' => function ($model) {
                return $model->type == self::TYPE_WAREHOUSING;
            }],
            ['method', 'in', 'range' => array_keys(self::warehousingOutOptions()), 'when' => function ($model) {
                return $model->type == self::TYPE_WAREHOUSING_OUT;
            }],
            ['warehouse_id', 'exist',
                'targetClass' => Warehouse::class,
                'targetAttribute' => 'id',
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'id',
            'warehouse_id' => '仓库',
            'number' => '流水单号',
            'type' => '类型',
            'method' => '出入库方式',
            'change_datetime' => '出入库时间',
            'remark' => '备注',
            'created_at' => '创建时间',
            'created_by' => '创建人',
        ];
    }

    /**
     * 类型选项
     *
     * @return array
     */
    public static function TypeOptions()
    {
        return [
            self::TYPE_WAREHOUSING => '出库',
            self::TYPE_WAREHOUSING_OUT => '出库',
            self::TYPE_WAREHOUSING_ALLOCATION => '调拨',
        ];
    }

    /**
     * 入库选项
     *
     * @return array
     */
    public static function warehousingOptions()
    {
        return [
            self::WAREHOUSING_MANUAL => '手工入库',
            self::WAREHOUSING_RETURN => '销售退货',
            self::WAREHOUSING_SURPLUS => '盘盈入库',
            self::WAREHOUSING_PURCHASE => '采购入库',
        ];
    }

    /**
     * 出库选项
     *
     * @return array
     */
    public static function warehousingOutOptions()
    {
        return [
            self::WAREHOUSING_OUT_MANUAL => '手动出库',
            self::WAREHOUSING_OUT_RETURN => '退货出库',
            self::WAREHOUSING_OUT_LOSS => '盘亏出库',
            self::WAREHOUSING_OUT_SALE => '销售出库',
        ];
    }

    /**
     * 仓库
     *
     * @return ActiveQuery
     */
    public function getWarehouse()
    {
        return $this->hasOne(Warehouse::class, ['id' => 'warehouse_id']);
    }

    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            if ($insert) {
                $this->number = date('YmdHis') . mt_rand(1000, 9999);
                $this->created_at = time();
                $this->created_by = Yii::$app->getUser()->getId();
            }
            $this->change_datetime = strtotime($this->change_datetime);

            return true;
        } else {
            return false;
        }
    }

}

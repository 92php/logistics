<?php

namespace app\modules\api\modules\wuliu\forms;

use app\jobs\Package2DxmJob;
use app\models\Constant;
use app\modules\api\modules\wuliu\models\Package;
use Yii;
use yii\base\Model;

/**
 * 包裹批量发货
 *
 * @package app\modules\api\modules\wuliu\forms

 */
class PackageBatchDeliveryForm extends Model
{

    const WEIGHT_COMPUTE_TYPE_NUMBER = 0; // 按数量计算重量差异
    const WEIGHT_COMPUTE_TYPE_PERCENT = 1; // 按百分比计算重量差异

    /**
     * @var array 批量发送的包裹
     */
    public $packages = [];

    /**
     * @var boolean 是否开启重量检测
     */
    public $enabled = false;

    /**
     * @var int 称重重量上限
     */
    public $max_weight = 1000;

    /**
     * @var int 称重差异计算方式：百分比/数量计算差异
     */
    public $compute_type = self::WEIGHT_COMPUTE_TYPE_PERCENT;

    /**
     * @var int 允许超重
     */
    public $weight_from = 10;

    /**
     * @var int 允许偏轻
     */
    public $weight_to = 10;

    public function rules()
    {
        return [
            ['packages', 'required'],
            [['max_weight', 'weight_from', 'weight_to'], 'required', 'when' => function ($model) {
                return $model->enabled == Constant::BOOLEAN_TRUE;
            }],
            [['max_weight', 'weight_from', 'weight_to', 'compute_type'], 'integer', 'min' => 0],
            ['enabled', 'boolean'],
            ['enabled', 'default', 'value' => Constant::BOOLEAN_TRUE],
            ['compute_type', 'default', 'value' => self::WEIGHT_COMPUTE_TYPE_NUMBER],
            ['compute_type', 'in', 'range' => array_keys(self::weightComputeTypeOptions())],
            ['packages', function ($attribute, $params) {
                $packages = $this->packages;
                if (is_array($packages)) {
                    $db = Yii::$app->getDb();
                    $hasError = false;
                    $errorMessage = null;
                    if ($this->enabled) {
                        foreach ($packages as $i => $package) {
                            $number = isset($package['waybill_number']) ? $package['waybill_number'] : null;
                            $weight = isset($package['weight']) ? $package['weight'] : 0;
                            if ($weight) {
                                if (is_numeric($weight)) {
                                    if ($weight > 10000) {
                                        $hasError = true;
                                        $errorMessage = "请检查{$package['waybill_number']}重量是否超过最大值10000g";
                                        break;
                                    }
                                } else {
                                    $hasError = true;
                                    $errorMessage = "请检查{$package['waybill_number']}重量是否为数字";
                                    break;
                                }
                            } else {
                                $hasError = true;
                                $errorMessage = "请设置{$package['waybill_number']}运单号称重重量";
                                break;
                            }

                            // 根据包裹获取商品，查询product表获取产品重量进行比对
                            $products = $db->createCommand("SELECT [[oi.quantity]],[[pr.weight]],[[p.waybill_number]],[[oi.product_name]] FROM {{%g_package}} p LEFT JOIN {{%g_package_order_item}} poi ON poi.package_id = p.id LEFT JOIN {{%g_order_item}} oi ON poi.order_item_id = oi.id LEFT JOIN {{%g_product}} pr ON pr.sku = oi.sku WHERE [[p.waybill_number]] = :waybillNumber", [":waybillNumber" => $number])->queryAll();
                            if ($products) {
                                foreach ($products as $product) {
                                    if ($product['weight'] != null) {
                                        if ($product['weight'] > 0) {
                                            // 检测重量异常规则
                                            if ($weight > $this->max_weight) {
                                                $hasError = true;
                                                $errorMessage = "包裹 [" . $package['waybill_number'] . "] 超过最大重量限制，请检查！";
                                            } else {
                                                if ($this->compute_type == self::WEIGHT_COMPUTE_TYPE_NUMBER) {
                                                    if ($weight < ($product['weight'] - $this->weight_to) * $product['quantity'] || $weight > ($product['weight'] + $this->weight_from) * $product['quantity']) {
                                                        $hasError = true;
                                                        $errorMessage = "包裹 [" . $package['waybill_number'] . "] 重量异常，请检查！";
                                                    }
                                                } elseif ($this->compute_type == self::WEIGHT_COMPUTE_TYPE_PERCENT) {
                                                    // 百分比差异计算
                                                    if ($weight < $product['weight'] * (1 - $this->weight_to / 100) * $product['quantity'] || $weight > $product['weight'] * (1 + $this->weight_from / 100) * $product['quantity']) {
                                                        $hasError = true;
                                                        $errorMessage = "包裹 [" . $package['waybill_number'] . "] 重量异常，请检查！";
                                                    }
                                                }
                                            }
                                        } else {
                                            $this->addError($attribute, "请前往店小蜜设置" . $product['waybill_number'] . "运单号下“{$product['product_name']}”商品重量。");
                                        }
                                    } else {
                                        $this->addError($attribute, "请前往店小蜜添加" . $product['waybill_number'] . "运单号下“{$product['product_name']}”商品相关重量。");
                                    }
                                }
                            } else {
                                $this->addError($attribute, "未找到此包裹。");
                            }
                        }
                        if ($hasError) {
                            $this->addError($attribute, $errorMessage);
                        }
                    }
                } else {
                    $this->addError($attribute, "包裹数据格式有误。");
                }
            }],
        ];
    }

    public function attributeLabels()
    {
        return [
            'packages' => '包裹列表',
            'max_weight' => '最大重量',
            'weight_from' => '允许超重',
            'weight_to' => '允许偏轻',
        ];
    }

    /**
     * 重量差异计算方式
     *
     * @return array
     */
    public static function weightComputeTypeOptions()
    {
        return [
            self::WEIGHT_COMPUTE_TYPE_NUMBER => '按数量计算',
            self::WEIGHT_COMPUTE_TYPE_PERCENT => '按百分比计算',
        ];
    }

    /**
     * 保存发货信息
     *
     * @return $this
     * @throws \yii\db\Exception
     */
    public function save()
    {
        if ($this->validate()) {
            $db = Yii::$app->getDb();
            $cmd = $db->createCommand();
            $queue = Yii::$app->queue;
            $now = time();
            $userId = Yii::$app->getUser()->getId();
            foreach ($this->packages as $package) {
                $columns = [
                    'weight' => $package['weight'],
                    'sync_status' => Package::SYNC_PENDING,
                    'status' => Package::STATUS_RECEIVED,
                    'weight_datetime' => isset($package['weight_datetime']) ? strtotime($package['weight_datetime']) : time(),
                    'delivery_datetime' => $now,
                    'updated_at' => $now,
                    'updated_by' => $userId,
                ];
                $cmd->update('{{%g_package}}', $columns, ['waybill_number' => $package['waybill_number']])->execute();
                $queue->push(new Package2DxmJob([
                    'id' => $package['id']
                ]));
            }
            Yii::$app->getResponse()->setStatusCode(201);
        } else {
            return $this;
        }
    }
}
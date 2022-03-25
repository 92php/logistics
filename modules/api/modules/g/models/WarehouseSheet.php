<?php

namespace app\modules\api\modules\g\models;

use Exception;
use Yii;
use yii\db\ActiveQuery;

/**
 * Class WarehouseSheet
 *
 * @package app\modules\api\modules\g\models
 */
class WarehouseSheet extends \app\modules\admin\modules\g\models\WarehouseSheet
{

    public $ext_details; // 详情

    private $_products; // 产品参数

    public function rules()
    {
        return array_merge(parent::rules(), [
            [['ext_details'], 'required'],
            ['ext_details', function ($attribute, $params) {
                if (is_array($this->ext_details)) {
                    $db = Yii::$app->getDb();
                    $rackCmd = $db->createCommand("SELECT COUNT(*) FROM {{%g_rack}} WHERE [[id]] = :id");
                    $stockCmd = $db->createCommand("SELECT [[product_id]], [[warehouse_id]],[[block]],[[rack_id]],[[usable_quantity]], [[price]], [[safely_quantity]] FROM {{%g_product_stock}} WHERE [[product_id]] = :productId");
                    foreach ($this->ext_details as $detail) {
                        if (!array_key_exists('rack_id', $detail)) {
                            $this->addError($attribute, "请选择对应货架。");
                            break;
                        } else {
                            $exists = $rackCmd->bindValue(':id', $detail['rack_id'])->queryScalar();
                            if (!$exists) {
                                $this->addError($attribute, "货架不存在，请检查。");
                            }
                        }
                        if (!array_key_exists('product_id', $detail)) {
                            $this->addError($attribute, "出入库单详情必须传入对应商品。");
                            break;
                        } else {
                            $products = $stockCmd->bindValue(':productId', $detail['product_id'])->queryAll();

                            if ($products) {
                                foreach ($products as $product) {
                                    if ($this->warehouse_id == $product['warehouse_id'] && $product['usable_quantity'] < $detail['quantity'] && $this->type == self::TYPE_WAREHOUSING_OUT) {
                                        $this->addError($attribute, "库存不足。");
                                        break;
                                    }
                                    $this->_products[$product['product_id']] = $product;
                                };
                            } else {
                                if ($this->type == self::TYPE_WAREHOUSING_OUT) {
                                    $this->addError($attribute, "商品不存在，不能发出库单，请检查。");
                                    break;
                                }
                            }
                        }
                        // 仓库藏品库存
                        $stockLoad = [
                            'product_id' => $detail['product_id'],
                            'warehouse_id' => $this->warehouse_id,
                            'block' => $detail['block'],
                            'rack_id' => $detail['rack_id'],
                            'safely_quantity' => $detail['safely_quantity'], // 安全库存
                            'trip_quantity' => isset($detail['trip_quantity']) ? $detail['trip_quantity'] : 0, // 在途库存
                            'usable_quantity' => isset($detail['quantity']) ? $detail['quantity'] : 0, // 可用库存
                            'actual_quantity' => isset($detail['quantity']) ? $detail['quantity'] : 0, //  实际库存,默认实际库存=可用库存
                            'price' => $detail['price'],
                            'remark' => $detail['remark']
                        ];
                        $productStock = ProductStock::find()->where([
                            'product_id' => $detail['product_id'], 'warehouse_id' => $this->warehouse_id, 'block' => $detail['block'], 'rack_id' => $detail['rack_id']
                        ])->one();

                        if ($productStock == null) {
                            $productStock = new ProductStock();
                        } else {
                            if ($this->type == self::TYPE_WAREHOUSING) {
                                // 入库
                                $stockLoad['usable_quantity'] = $productStock->usable_quantity + $detail['quantity']; // 可用库存 + 入库库存
                            } elseif ($this->type == self::TYPE_WAREHOUSING_OUT) {
                                // 出库
                                $stockLoad['price'] = $productStock->price;
                                $stockLoad['usable_quantity'] = $productStock->usable_quantity - $detail['quantity'];// 可用库存 - 入库库存
                                $stockLoad['actual_quantity'] = $stockLoad['usable_quantity'] + $productStock->booking_quantity; // 可用库存 + 预售库存
                                if ($stockLoad['actual_quantity'] < 0) {
                                    // 可用胡村小于0
                                    $this->addError($attribute, "仓库商品可用库存不足。");
                                }
                            }
                        }

                        $productStock->load($stockLoad, '');
                        if ($productStock->validate() === false || $productStock->hasErrors()) {
                            $this->addError($attribute, $productStock->errors);
                        }
                    }
                } else {
                    $this->addError($attribute, "出入库单详情格式错误。");
                }
            }]
        ]);
    }

    public function fields()
    {
        return [
            'id',
            'warehouse_id',
            'number',
            'type',
            'method',
            'change_datetime',
            'remark',
            'created_at',
            'created_by',
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

    /**
     * 详情
     *
     * @return ActiveQuery
     */
    public function getDetails()
    {
        return $this->hasMany(WarehouseSheetDetail::class, ['warehouse_id' => 'id']);
    }

    public function extraFields()
    {
        return [
            'warehouse',
            'details',
        ];
    }

    /**
     * @param bool $insert
     * @param array $changedAttributes
     * @return bool|void
     */
    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);
        if ($insert) {
            $transaction = Yii::$app->db->beginTransaction();
            try {
                $isSuccess = false;
                // 产品出入库记录
                if ($this->ext_details) {
                    foreach ($this->ext_details as $detail) {
                        $oldStockQuantity = 0;
                        $oldPrice = 0;
                        if ($this->warehouse_id == $this->_products[$detail['product_id']]['warehouse_id']) {
                            $oldStockQuantity = $this->_products[$detail['product_id']]['usable_quantity'];
                            $oldPrice = $this->_products[$detail['product_id']]['price'];
                        }
                        $sheetQuantity = $detail['quantity'];
                        if ($this->type == self::TYPE_WAREHOUSING_OUT) {
                            // 出库
                            $sheetQuantity = -1 * $sheetQuantity; // 出库的话数量为负数
                        }
                        // 出入库详情单
                        $model = new WarehouseSheetDetail();
                        $detailLoad = [
                            'warehouse_sheet_id' => $this->id,
                            'warehouse_id' => $this->warehouse_id,
                            'rack_id' => $detail['rack_id'],
                            'product_id' => $detail['product_id'],
                            'before_stock_quantity' => $oldStockQuantity, // 原库存
                            'change_quantity' => $sheetQuantity, // 变动数量
                            'after_stock_quantity' => $oldStockQuantity + $sheetQuantity, // 新库存=原库存+变动数量
                            'before_price' => $oldPrice, // 原单价
                            'change_price' => $detail['price'],
                            'after_price' => $this->type == self::TYPE_WAREHOUSING ? ($oldStockQuantity * $oldPrice + $detail['price'] * $sheetQuantity) / ($oldStockQuantity + $sheetQuantity) : 0, //(原库存 * 原单价 + 入库价 * 变动数量) / (原库存 + 变动数量)
                        ];

                        if ($model->load($detailLoad, '')) {
                            $isSuccess = $model->save();
                            if (!$isSuccess) {
                                break;
                            }
                        }

                        // 仓库产品库存
                        $stockLoad = [
                            'product_id' => $detail['product_id'],
                            'warehouse_id' => $this->warehouse_id,
                            'block' => $detail['block'],
                            'rack_id' => $detail['rack_id'],
                            'safely_quantity' => $detail['safely_quantity'], // 安全库存
                            'trip_quantity' => isset($detail['trip_quantity']) ? $detail['trip_quantity'] : 0, // 在途库存
                            'usable_quantity' => isset($detail['quantity']) ? $detail['quantity'] : 0, // 可用库存
                            'actual_quantity' => isset($detail['quantity']) ? $detail['quantity'] : 0, //  实际库存,默认实际库存=可用库存
                            'price' => $detailLoad['after_price'], // 价格和 出入库报表详情中的新单价是一样的
                            'remark' => $detail['remark']
                        ];
                        $productStock = ProductStock::find()->where([
                            'product_id' => $detail['product_id'], 'warehouse_id' => $this->warehouse_id, 'block' => $detail['block'], 'rack_id' => $detail['rack_id']
                        ])->one();
                        if ($productStock == null) {
                            $productStock = new ProductStock();
                        } else {
                            if ($this->type == self::TYPE_WAREHOUSING) {
                                // 入库
                                $stockLoad['usable_quantity'] = $productStock->usable_quantity + $detail['quantity']; // 可用库存 + 入库库存
                            } elseif ($this->type == self::TYPE_WAREHOUSING_OUT) {
                                // 出库
                                $stockLoad['price'] = $productStock->price;
                                $stockLoad['safely_quantity'] = $productStock->safely_quantity;
                                $stockLoad['usable_quantity'] = $productStock->usable_quantity - $detail['quantity'];// 可用库存 - 入库库存

                            }
                            $stockLoad['actual_quantity'] = $stockLoad['usable_quantity'] + $productStock->booking_quantity; // 可用库存 + 预售库存

                        }

                        $productStock->load($stockLoad, '');
                        if ($productStock->save()) {
                            // 如果更新或者添加成功，需要更新$this->_products中的数据
                            $this->_products[$productStock->product_id] = [
                                'product' => $productStock->product_id,
                                'warehouse_id' => $productStock->warehouse_id,
                                'block' => $productStock->block,
                                'rack_id' => $productStock->rack_id,
                                'usable_quantity' => $productStock->usable_quantity,
                                'price' => $productStock->price
                            ];
                        } else {
                            $isSuccess = false;
                            break;
                        }

                        // 产品库存修改
                        $productModel = Product::findOne(['id' => $detail['product_id']]);

                        $avg_price = 0;

                        if ($this->type == self::TYPE_WAREHOUSING) {
                            // 入库
                            $productModel->stock_quantity += $detail['quantity'];
                        } elseif ($this->type == self::TYPE_WAREHOUSING_OUT) {
                            // 出库
                            $productModel->stock_quantity -= $detail['quantity'];
                        }
                        foreach ($this->_products as $product) {
                            $avg_price += $product['price'];
                        }
                        // 计算产品平均价
                        $productModel->cost_price = round($avg_price / count($this->_products), 2);
                        $isSuccess = $productModel->save();
                        if (!$isSuccess) {
                            break;
                        }
                    }
                    if ($isSuccess) {
                        $transaction->commit();
                    } else {
                        $transaction->rollBack();
                    }
                }
            } catch (Exception $e) {
                $transaction->rollBack();
            }

            return true;
        }
    }

}

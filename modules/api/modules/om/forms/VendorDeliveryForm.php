<?php

namespace app\modules\api\modules\om\forms;

use app\modules\api\modules\om\models\OrderItemRoute;
use app\modules\api\modules\om\models\Package;
use Yii;
use yii\base\Model;
use function var_dump;

/**
 * 供应商发货表单
 *
 * @package app\modules\api\modules\om\forms
 */
class VendorDeliveryForm extends Model
{

    /**
     * @var  int 运单号
     */
    public $waybill_number;

    /**
     * @var int 包裹号
     */
    public $package_id;

    /**
     * @var string 物流公司
     */
    public $logistics_company;

    public function rules()
    {
        return [
            [['waybill_number', 'package_id'], 'required'],
            ['waybill_number', 'string', 'max' => 20],
            ['package_id', 'integer'],
            ['logistics_company', 'string', 'max' => 40],
            ['package_id', function ($attribute, $params) {
                // 先判断package 是否发货，没发货 判断是否有商品
                $db = Yii::$app->getDb();
                $status = $db->createCommand("SELECT [[status]] FROM {{%om_package}} WHERE [[id]] = :id", [':id' => $this->package_id])->queryScalar();
                if ($status) {
                    $this->addError($attribute, '包裹已经发货，不可再次发货');
                }
                $count = $db->createCommand("SELECT COUNT(*) FROM {{%om_order_item_route}} WHERE [[package_id]] = :packageId", [':packageId' => $this->package_id])->queryScalar();
                if (!$count) {
                    $this->addError($attribute, '包裹下没有商品，请选定商品后发货');
                }
            }]
        ];
    }

    /**
     * @return bool
     * @throws \Throwable
     */
    public function save()
    {
        $isSuccess = false;
        $transaction = Yii::$app->db->beginTransaction();
        try {
            // 发货成功，保存运单号和发货时间，更新商品状态为已发货
            $routes = OrderItemRoute::find()->where(['package_id' => $this->package_id])->all();
            foreach ($routes as $route) {
                $route->vendor_deliver_at = time();
                $route->delivery_status = OrderItemRoute::STATUS_DELIVERY_TRUE;
                $route->current_node = OrderItemRoute::NODE_ALREADY_SHIPPED;
                $isSuccess = $route->save();
            }

            if ($package = Package::findOne(['id' => $this->package_id])) {
                $package->waybill_number = $this->waybill_number;
                $package->status = Package::STATUS_DELIVER;
                $package->logistics_company = $this->logistics_company;
                $isSuccess = $package->save();
            }
            if ($isSuccess) {
                $transaction->commit();
            } else {
                $transaction->rollBack();
            }

            return true;
        } catch (\Exception $e) {
            $transaction->rollBack();
            throw $e;
        } catch (\Throwable $e) {
            $transaction->rollBack();
            throw $e;
        }
    }
}
<?php

namespace app\modules\api\modules\om\forms;

use app\modules\api\modules\om\models\OrderItemBusiness;
use app\modules\api\modules\om\models\OrderItemRoute;
use app\modules\api\modules\om\models\Package;
use Yii;
use yii\base\Model;
use function in_array;

/**
 * Class RemoveProductForm
 * 包裹移除商品
 *
 * @package app\modules\api\modules\om\forms
 */
class RemoveProductForm extends Model
{

    /**
     * 包裹id
     *
     * @var int
     */
    public $package_id;

    /**
     * 路由id
     *
     * @var int
     */
    public $route_id;

    public function rules()
    {
        return [
            [['package_id', 'route_id'], 'required'],
            ['package_id', 'exist',
                'targetClass' => Package::class,
                'targetAttribute' => 'id',
            ],
            ['route_id', function ($attribute, $params) {
                $routeExists = Yii::$app->getDb()->createCommand("SELECT [[r.package_id]], [[r.order_item_id]], [[r.current_node]] FROM {{%om_order_item_route}} r INNER JOIN {{%om_order_item_business}} b ON r.order_item_id = b.order_item_id WHERE [[r.id]] = :id AND [[b.status]] = :status", [':id' => $this->route_id, ':status' => OrderItemBusiness::STATUS_IN_HANDLE])->queryOne();
                if ($routeExists) {
                    if ($routeExists['package_id'] != $this->package_id) {
                        $this->addError($attribute, '此订单不在该包裹下。');
                    }
                    if (!in_array($routeExists['current_node'], [OrderItemRoute::NODE_STAY_SHIPPED, OrderItemRoute::NODE_ALREADY_PRODUCED])) {
                        $this->addError($attribute, '此订单不在生产中或者待发货，无法移除。');
                    }
                } else {
                    $this->addError($attribute, '未找到此订单路由。');
                }
            }],
            ['package_id', function ($attribute, $params) {
                $status = Yii::$app->getDb()->createCommand("SELECT [[status]] FROM {{%om_package}} WHERE [[id]] = :id", [':id' => $this->package_id])->queryScalar();
                // 如果为1
                if ($status) {
                    {
                        $this->addError($attribute, '已发货包裹不能移除商品。');
                    }
                };
            }]
        ];
    }

    /**
     * 保存
     *
     * @throws \Exception
     * @throws \Throwable
     */
    public function save()
    {
        $isSuccess = false;
        $transaction = Yii::$app->db->beginTransaction();
        try {
            $model = OrderItemRoute::findOne(['id' => $this->route_id]);
            $packageModel = Package::findOne(['id' => $model->package_id]);
            $model->package_id = 0;
            $model->current_node = OrderItemRoute::NODE_ALREADY_PRODUCED;
            $isSuccess = $model->save();
            $packageModel->items_quantity -= 1;
            $packageModel->remaining_items_quantity -= 1;
            $isSuccess = $packageModel->save();
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

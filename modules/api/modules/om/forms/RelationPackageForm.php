<?php

namespace app\modules\api\modules\om\forms;

use app\modules\api\models\Constant;
use app\modules\api\modules\om\models\OrderItemBusiness;
use app\modules\api\modules\om\models\OrderItemRoute;
use app\modules\api\modules\om\models\Package;
use Yii;
use yii\base\Model;
use yii\db\Query;
use function array_diff;
use function explode;
use function in_array;
use function var_dump;

/**
 * Class RelationPackageForm
 * 包裹号商品关联
 *
 * @package app\modules\api\modules\om\forms
 */
class RelationPackageForm extends Model
{

    /**
     * 路由id
     *
     * @var array
     */
    public $route_ids = [];

    /**
     * 包裹id
     *
     * @var integer
     */
    public $package_id;

    /**
     * 订单号
     *
     * @var string
     */
    public $numbers;

    public function rules()
    {
        return [
            [['package_id', 'numbers'], 'required'],
            [['package_id'], 'integer'],
            ['numbers', 'string'],
            ['package_id', 'exist',
                'targetClass' => Package::class,
                'targetAttribute' => 'id',
            ],
            ['numbers', function ($attribute, $params) {
                $numbers = explode(" ", $this->numbers);
                if ($numbers) {
                    $routes = (new Query())->select(['r.id', 'o.number'])->from("{{%om_order_item_route}} r")
                        ->innerJoin("{{%om_order_item_business}} b", "r.order_item_id = b.order_item_id")
                        ->innerJoin("{{%g_order_item}} oi", "oi.id = b.order_item_id")
                        ->innerJoin("{{%g_order}} o", "o.id = oi.order_id")
                        ->innerJoin("{{%g_vendor}} v", 'v.id = r.vendor_id')
                        ->innerJoin("{{%g_vendor_member}} vm", 'vm.vendor_id = v.id')
                        ->where(['r.current_node' => OrderItemRoute::NODE_ALREADY_PRODUCED, 'b.status' => OrderItemBusiness::STATUS_IN_HANDLE, 'r.package_id' => 0, 'vm.member_id' => Yii::$app->getUser()->getId(),])->andWhere(['IN', 'o.number', $numbers])->all();

                    if ($routes) {
                        $numberArr = [];
                        foreach ($routes as $item) {
                            $numberArr[] = $item['number'];
                        }
                        $diff = array_diff($numbers, $numberArr);
                        if ($diff) {
                            $message = "订单出现错误，请检查订单号";
                            foreach ($diff as $item) {
                                $message .= " ({$item}) ";
                            }
                            $this->addError($attribute, $message);
                        } else {
                            foreach ($routes as $item) {
                                $this->route_ids[] = $item['id'];
                            }
                        }
                    } else {
                        $this->addError($attribute, '请检查当前订单状态，是否绑定包裹、是否在生产中、是否是当前供应商订单。');
                    }
                } else {
                    $this->addError($attribute, '订单号有误，请检查。');
                }
            }]
        ];
    }

    /**
     * 保存
     *
     * @throws \Exception
     * @throws Throwable
     */
    public function save()
    {
        $isSuccess = false;
        $transaction = Yii::$app->db->beginTransaction();
        try {
            foreach ($this->route_ids as $route_id) {
                $routeModel = OrderItemRoute::findOne(['id' => $route_id]);
                $routeModel->package_id = $this->package_id;
                $routeModel->current_node = OrderItemRoute::NODE_STAY_SHIPPED;
                $isSuccess = $routeModel->save();
                $packageModel = Package::findOne(['id' => $this->package_id]);
                $packageModel->items_quantity += 1;
                $packageModel->remaining_items_quantity += 1;
                $isSuccess = $packageModel->save();
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
        } catch (Throwable $e) {
            $transaction->rollBack();
            throw $e;
        }
    }
}
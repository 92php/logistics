<?php

namespace app\modules\api\modules\om\models;

use app\models\Constant;
use app\modules\api\modules\g\models\Shop;
use DateTime;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use yii\data\Pagination;
use yii\db\Query;

class OrderSearch extends Order
{

    public $sku; // sku
    public $productName; // 产品名
    public $vendor; // 供应商
    public $customized;  // 定制信息
    public $itemStatus;  // 详情状态
    public $payment_begin_at; // 付款开始时间
    public $payment_end_at;  // 付款结束时间
    public $total_amount_begin; // 金额
    public $total_amount_end;
    public $_status;
    public $item_place_order_begin_at; //商品下单开始时间
    public $item_place_order_end_at; //商品下单结束时间

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'status', 'platform_id', 'place_order_at', 'payment_at', 'itemStatus', '_status', 'shop_id', 'country_id'], 'integer'],
            [['total_amount_begin', 'total_amount_end'], 'number'],
            [['number', 'consignee_name', 'consignee_state', 'consignee_city', 'consignee_postcode', 'vendor', 'productName', 'customized', 'sku', 'payment_begin_at', 'payment_end_at', 'item_place_order_begin_at', 'item_place_order_end_at'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function scenarios()
    {
        // bypass scenarios() implementation in the parent class
        return Model::scenarios();
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     * @throws \Exception
     */
    public function search($params)
    {
        $query = Order::find()
            ->where(['platform_id' => Constant::PLATFORM_SHOPIFY, 'product_type' => Shop::PRODUCT_TYPE_CUSTOMIZED])
            ->andWhere(['>', 'payment_at', '1590767999']); // > 2020-05-29 23:59:59

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => [
                    'id' => SORT_DESC,
                    'payment_at' => SORT_DESC,
                ],
            ],
            'pagination' => new Pagination([
                'pageSizeLimit' => [1, 200]
            ])
        ]);

        $this->load($params, '');

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'id' => $this->id,
            'status' => $this->status,
            'platform_id' => $this->platform_id,
            'shop_id' => $this->shop_id,
        ]);

        $query->andFilterWhere(['like', 'consignee_name', $this->consignee_name])
            ->andFilterWhere(['like', 'country_id', $this->country_id])
            ->andFilterWhere(['like', 'consignee_state', $this->consignee_state])
            ->andFilterWhere(['like', 'consignee_city', $this->consignee_city])
            ->andFilterWhere(['like', 'consignee_postcode', $this->consignee_postcode]);

        if ($this->number) {
            $this->number = str_replace(" ", " ", $this->number);
            $query->andWhere(['IN', 'number', explode(' ', $this->number)]);
        }
        if ($this->payment_begin_at && $this->payment_end_at) {
            $query->andWhere(['BETWEEN', 'payment_at',
                (new DateTime($this->payment_begin_at))->setTime(0, 0, 0)->getTimestamp(),
                (new DateTime($this->payment_end_at))->setTime(23, 59, 59)->getTimestamp()]);
        }

        if ($this->total_amount_begin && $this->total_amount_end) {
            $query->andWhere(['BETWEEN', 'total_amount',
                $this->total_amount_begin,
                $this->total_amount_end
            ]);
        }

        // 如果有sku
        if ($this->sku) {
            $query->andWhere(['IN', 'id', (new Query())->select(['order_id'])->from('{{%g_order_item}}')->where(['like', 'sku', $this->sku])]);
        }
        // 如果有产品名
        if ($this->productName) {
            $query->andWhere(['IN', 'id', (new Query())->select(['order_id'])->from('{{%g_order_item}}')->where(['like', 'product_name', $this->productName])]);
        }
        // 如果有供应商
        if ($this->vendor) {
            $query->andWhere(['IN', 'id', (new Query())->select(['order_id'])->from('{{%g_order_item}}')->where(['vendor_id' => $this->vendor])]);
        }
        // 如果有状态
        if ($this->itemStatus || $this->itemStatus === 0) {
            // 如果是待核实/待下单，搜索item_business表；如果不是，查询route表
            if (in_array($this->itemStatus, [OrderItemBusiness::STATUS_STAY_CHECK, OrderItemBusiness::STATUS_STAY_PLACE_ORDER])) {
                $query->andWhere(['IN', 'id', (new Query())
                    ->select(['oi.order_id'])
                    ->from('{{%g_order_item}} oi')
                    ->innerJoin("{{%om_order_item_business}} ob", 'ob.order_item_id=oi.id')
                    ->where(['ob.status' => $this->itemStatus])]);
            } else {
                $query->andWhere(['IN', 'id', (new Query())->select('order_id')->from(OrderItem::tableName())->where(['IN', 'id', (new Query())->select(['order_item_id'])->from(OrderItemRoute::tableName())->where(['current_node' => $this->itemStatus])])]);
            }
        }
        //　如果有定制信息
        if ($this->customized) {
            $jsonSql = "jSON_CONTAINS(LOWER(extend->'$.names'), JSON_ARRAY(";
            $a = [];
            $paramsExtend = [];
            foreach (explode(',', strtolower($this->customized)) as $i => $item) {
                $a[] = ":L{$i}";
                $paramsExtend[":L{$i}"] = $item;
            }
            $jsonSql .= implode(",", $a) . '))';

            $query->andFilterWhere(['IN', 'id', (new Query())->select(['order_id'])->from(OrderItem::tableName())->where($jsonSql, $paramsExtend)]);
        }

        $isCurrentNode = false;
        $currentNode = 0;
        // 订单状态查询
        if ($this->_status) {
            switch ($this->_status) {
                case 1:
                    //已付款
                    $query->andWhere(['IN', 'id', (new Query())->select(['oi.order_id'])
                        ->from('{{%g_order_item}} oi')
                        ->innerJoin("{{%om_order_item_business}} bi", 'bi.order_item_id = oi.id')
                        ->where(['bi.status' => OrderItemBusiness::STATUS_STAY_CHECK, 'oi.ignored' => Constant::BOOLEAN_FALSE])]);
                    break;
                case 2:
                    //待下单
                    $query->andWhere(['IN', 'id', (new Query())->select(['oi.order_id'])
                        ->from('{{%g_order_item}} oi')
                        ->innerJoin("{{%om_order_item_business}} bi", 'bi.order_item_id = oi.id')
                        ->where(['bi.status' => OrderItemBusiness::STATUS_STAY_PLACE_ORDER, 'oi.ignored' => Constant::BOOLEAN_FALSE])]);
                    break;
                case 3:
                    $isCurrentNode = true;
                    $currentNode = OrderItemRoute::NODE_STAY_RECEIPT;
                    //等待供应商接单
                    $query->andWhere(['IN', 'id', (new Query())
                        ->select('oi.order_id')
                        ->from("{{%g_order_item}} oi")
                        ->innerJoin("{{%om_order_item_route}} oir", 'oir.order_item_id = oi.id')
                        ->where(['oir.current_node' => $currentNode, 'oi.ignored' => Constant::BOOLEAN_FALSE])]);
                    break;
                case 4:
                    $isCurrentNode = true;
                    $currentNode = OrderItemRoute::NODE_TO_PRODUCED;
                    //下单成功
                    $query->andWhere(['IN', 'id', (new Query())
                        ->select('oi.order_id')
                        ->from("{{%g_order_item}} oi")
                        ->innerJoin("{{%om_order_item_route}} oir", 'oir.order_item_id = oi.id')
                        ->where(['oir.current_node' => OrderItemRoute::NODE_TO_PRODUCED, 'oi.ignored' => Constant::BOOLEAN_FALSE])]);
                    break;
                case 5:
                    //下单失败
                    $query->andWhere(['IN', 'id', (new Query())->select(['oi.order_id'])
                        ->from('{{%g_order_item}} oi')
                        ->innerJoin('{{%om_order_item_business}} oib', 'oib.order_item_id = oi.id')
                        ->where(['oib.status' => OrderItemBusiness::STATUS_STAY_REJECT, 'oi.ignored' => Constant::BOOLEAN_FALSE])]);
                    break;
                case 6:
                    $isCurrentNode = true;
                    $currentNode = OrderItemRoute::NODE_ALREADY_PRODUCED;
                    //投入生产
                    $query->andWhere(['IN', 'id', (new Query())->select('oi.order_id')
                        ->from("{{%g_order_item}} oi")
                        ->innerJoin("{{%om_order_item_route}} oir", 'oir.order_item_id = oi.id')
                        ->where(['oir.current_node' => OrderItemRoute::NODE_ALREADY_PRODUCED, 'oi.ignored' => Constant::BOOLEAN_FALSE])]);
                    break;
                case 7:
                    //取消订单
                    $query->andWhere(['IN', 'id', (new Query())->select('oi.order_id')->from("{{%om_order_item_route_cancel_log}} rcl")
                        ->innerJoin("{{%om_order_item_route}} oir", 'rcl.order_item_route_id=oir.id')
                        ->innerJoin("{{%g_order_item}} oi", 'oir.order_item_id = oi.id')]);
                    break;
                case 8:
                    $isCurrentNode = true;
                    $currentNode = OrderItemRoute::NODE_ALREADY_SHIPPED;
                    //发货
                    $query->andWhere(['IN', 'id', (new Query())->select('oi.order_id')
                        ->from("{{%g_order_item}} oi")
                        ->innerJoin("{{%om_order_item_route}} oir", 'oir.order_item_id = oi.id')
                        ->where(['oir.current_node' => OrderItemRoute::NODE_ALREADY_SHIPPED, 'oi.ignored' => Constant::BOOLEAN_FALSE])]);
                    break;
                case 9:
                    //完成
                    $query->andWhere(['IN', 'id', (new Query())->select('oi.order_id')
                        ->from("{{%g_order_item}} oi")
                        ->innerJoin("{{%om_order_item_business}} bi", 'bi.order_item_id = oi.id')
                        ->where(['bi.status' => OrderItemBusiness::STATUS_STAY_COMPLETE, 'oi.ignored' => Constant::BOOLEAN_FALSE])]);
                    break;
            }
        }

        if ($this->item_place_order_begin_at && $this->item_place_order_end_at) {
            $sql = (new Query())->select(['oi.order_id'])
                ->from("{{%om_order_item_route}} r")
                ->innerJoin("{{%om_order_item_business}} ob", '[[r.order_item_id]] = [[ob.order_item_id]]')
                ->innerJoin("{{%g_order_item}} oi", '[[oi.id]] = [[r.order_item_id]]')
                ->where(["BETWEEN", 'r.place_order_at',
                    (new DateTime($this->item_place_order_begin_at))->setTime(0, 0, 0)->getTimestamp(),
                    (new DateTime($this->item_place_order_end_at))->setTime(23, 59, 59)->getTimestamp()
                ])
                ->andWhere(['IN', 'ob.status', [OrderItemBusiness::STATUS_STAY_PLACE_ORDER, OrderItemBusiness::STATUS_IN_HANDLE, OrderItemBusiness::STATUS_STAY_COMPLETE, OrderItemBusiness::STATUS_STAY_REJECT]]);

            if ($isCurrentNode) {
                $sql->andWhere(['r.current_node' => $currentNode]);
            }
            $query->andWhere(['IN', 'id', $sql]);
        }

        return $dataProvider;
    }

}

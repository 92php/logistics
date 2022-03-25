<?php

namespace app\modules\api\modules\om\models;

use DateTime;
use yadjet\helpers\IsHelper;
use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use yii\data\Pagination;
use yii\db\Query;

class OrderItemRouteSearch extends OrderItemRoute
{

    /**
     * @var $current_node int 商品状态
     */
    public $current_node;

    /**
     * @var $sku string 商品SKU
     */
    public $sku;

    /**
     * @var $customized string 商品定制信息
     */
    public $customized;

    /**
     * @var $product_name string 商品名称
     */
    public $product_name;

    /**
     * @var $number string 订单号
     */
    public $number;

    /**
     * @var $package_number string 包裹号
     */
    public $package_number;

    /**
     * @var $payment_begin_datetime string 筛选付款开始时间
     */
    public $payment_begin_datetime;

    /**
     * @var $payment_end_datetime string 筛选付款结束时间
     */
    public $payment_end_datetime;

    /**
     * 是否是仓库
     *
     * @var int
     */
    public $is_warehouse;

    /**
     * 完成时间开始
     *
     * @var string
     */
    public $warehousing_begin_at;

    /**
     * 完成时间结束
     *
     * @var string
     */
    public $warehousing_end_at;

    /**
     * 运单号
     *
     * @var string
     */
    public $waybill_number;

    /**
     * @var string 下单开始时间
     */
    public $place_order_begin_at;

    /**
     * @var string 下单结束时间
     */
    public $place_order_end_at;

    /**
     * 商品优先级
     *
     * @var int
     */
    public $priority;

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'order_item_id', 'place_order_at', 'place_order_by', 'vendor', 'receipt_at', 'receipt_status', 'production_at', 'vendor_deliver_at', 'receiving_at', 'inspection_at', 'warehousing_at', 'inspection_number', 'is_reissue', 'is_accord_with', 'is_information_match', 'parent_id', 'is_warehouse', 'is_print', 'is_export', 'priority'], 'integer'],
            [['reason', 'feedback', 'information_feedback', 'sku', 'payment_begin_datetime', 'payment_end_datetime', 'customized', 'product_name', 'number', 'package_number', 'warehousing_begin_at', 'warehousing_end_at', 'waybill_number', 'place_order_begin_at', 'place_order_end_at', 'current_node'], 'safe'],
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
        $query = OrderItemRoute::find();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => [
                    'id' => SORT_DESC,
                ],
            ],
            'pagination' => new Pagination([
                'pageSizeLimit' => [1, 200]
            ])
        ]);

        $this->load($params, '');
        if (!$this->is_warehouse) {
            // 如果不是仓库，则获取当前供应商
            $query->innerJoin("{{%g_vendor}} v", 'v.id = vendor_id')
                ->innerJoin("{{%g_vendor_member}} vm", 'vm.vendor_id = v.id')
                ->where(['vm.member_id' => Yii::$app->getUser()->getId()]);
        }

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'id' => $this->id,
            'order_item_id' => $this->order_item_id,
            'place_order_at' => $this->place_order_at,
            'place_order_by' => $this->place_order_by,
            'vendor_id' => $this->vendor_id,
            'parent_id' => $this->parent_id,
            'receipt_at' => $this->receipt_at,
            'receipt_status' => $this->receipt_status,
            'production_at' => $this->production_at,
            'vendor_deliver_at' => $this->vendor_deliver_at,
            'receiving_at' => $this->receiving_at,
            'inspection_at' => $this->inspection_at,
            'warehousing_at' => $this->warehousing_at,
            'inspection_number' => $this->inspection_number,
            'is_reissue' => $this->is_reissue,
            'is_accord_with' => $this->is_accord_with,
            'is_information_match' => $this->is_information_match,
            'is_print' => $this->is_print,
            'is_export' => $this->is_export
        ]);

        if ($this->current_node) {
            // 查询route状态
            $query->andFilterWhere(['IN', 'current_node', explode(',', $this->current_node)]);
        } else {
            $query->andFilterWhere(['NOT', ['current_node' => OrderItemRoute::NODE_ALREADY_CANCEL]]);
        }
        if ($this->package_number) {
            $query->andFilterWhere(['IN', 'package_id', (new Query())->select(['id'])->from(Package::tableName())->where(['number' => explode(',', $this->package_number)])]);
        }
        if ($this->number) {
            $query->andFilterWhere(['IN', 'order_item_id', (new Query())->select(['oi.id'])->from("{{%g_order_item}} oi")
                ->innerJoin("{{%g_order}} o", 'o.id = oi.order_id')
                ->where(['IN', 'o.number', explode(' ', $this->number)])]);
        }

        // 优先级
        if ($this->priority) {
            $query->andWhere(['order_item_id' => (new Query())->select(['order_item_id'])->from("{{%om_order_item_business}}")->where(['priority' => $this->priority])]);
        }

        if ($this->product_name) {
            $query->andFilterWhere(['IN', 'order_item_id', (new Query())->select(['id'])->from(OrderItem::tableName())->where(['LIKE', 'product_name', $this->product_name])]);
        }
        if ($this->sku) {
            $query->andFilterWhere(['IN', 'order_item_id', (new Query())->select(['id'])->from(OrderItem::tableName())->where(['LIKE', 'sku', $this->sku])]);
        }
        if ($this->customized) {
            $jsonSql = "jSON_CONTAINS(LOWER(extend->'$.names'), JSON_ARRAY(";
            $a = [];
            $paramsExtend = [];
            foreach (explode(',', strtolower($this->customized)) as $i => $item) {
                $a[] = ":L{$i}";
                $paramsExtend[":L{$i}"] = $item;
            }
            $jsonSql .= implode(",", $a) . '))';

            $query->andFilterWhere(['IN', 'order_item_id', (new Query())->select(['id'])->from(OrderItem::tableName())->where($jsonSql, $paramsExtend)]);
        }
        if (
            $this->payment_end_datetime &&
            $this->payment_begin_datetime &&
            IsHelper::datetime($this->payment_begin_datetime) &&
            IsHelper::datetime($this->payment_end_datetime)
        ) {
            $query->andWhere(['IN', 'order_item_id', (new Query)->select(['id'])->from(OrderItem::tableName())->where(['order_id' => (new Query())->select(['id'])->from(Order::tableName())->where(['BETWEEN', 'payment_at',
                (new DateTime($this->payment_begin_datetime))->setTime(0, 0, 0)->getTimestamp(),
                (new DateTime($this->payment_end_datetime))->setTime(23, 59, 59)->getTimestamp()])])]);
        }
        // 完成入库时间区间查询
        if (
            $this->warehousing_begin_at &&
            $this->warehousing_end_at &&
            IsHelper::datetime($this->warehousing_begin_at) &&
            IsHelper::datetime($this->warehousing_end_at)
        ) {
            $query->andWhere(['BETWEEN', 'warehousing_at',
                (new DateTime($this->warehousing_begin_at))->setTime(0, 0, 0)->getTimestamp(),
                (new DateTime($this->warehousing_end_at))->setTime(23, 59, 59)->getTimestamp()]);
        }

        // 下单时间区间搜索
        if (
            $this->place_order_begin_at &&
            $this->place_order_end_at &&
            IsHelper::datetime($this->place_order_begin_at) &&
            IsHelper::datetime($this->place_order_end_at)
        ) {
            $query->andWhere(['BETWEEN', 'place_order_at',
                (new DateTime($this->place_order_begin_at))->setTime(0, 0, 0)->getTimestamp(),
                (new DateTime($this->place_order_end_at))->setTime(23, 59, 59)->getTimestamp()]);
        }
        // 运单号查询
        if ($this->waybill_number) {
            $query->andWhere(['package_id' => (new Query())->select(['id'])->from("{{%om_package}}")->where(['waybill_number' => $this->waybill_number])]);
        }

        $query->andFilterWhere(['like', 'reason', $this->reason])
            ->andFilterWhere(['like', 'feedback', $this->feedback])
            ->andFilterWhere(['like', 'information_feedback', $this->information_feedback]);

        return $dataProvider;
    }

}

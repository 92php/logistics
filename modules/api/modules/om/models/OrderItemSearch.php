<?php

namespace app\modules\api\modules\om\models;

use app\modules\api\models\Constant;
use DateTime;
use yadjet\helpers\IsHelper;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use yii\db\Query;

class OrderItemSearch extends OrderItem
{

    /**
     * @var $status int 商品状态
     */
    public $status;

    /**
     * @var $number string 订单号
     */
    public $number;

    /**
     * @var $payment_begin_datetime string 筛选付款开始时间
     */
    public $payment_begin_datetime;

    /**
     * @var $payment_end_datetime string 筛选付款结束时间
     */
    public $payment_end_datetime;

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'status'], 'integer'],
            [['number', 'product_name', 'sku', 'payment_begin_datetime', 'payment_end_datetime', 'extend'], 'safe'],
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
        $query = OrderItem::find()->where(['ignored' => Constant::BOOLEAN_FALSE]);

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => [
                    'id' => SORT_DESC,
                ],
            ],
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
        ]);
        if ($this->status) {
            $query->andFilterWhere(['IN', 'id', (new Query())->select(['order_item_id'])->from(OrderItemBusiness::tableName())->where(['status' => $this->status])]);
        }
        if ($this->number) {
            $query->andFilterWhere(['order_id' => (new Query())->select(['id'])->from(Order::tableName())->where(['number' => $this->number])]);
        }
        if (
            $this->payment_end_datetime &&
            $this->payment_begin_datetime &&
            IsHelper::datetime($this->payment_begin_datetime) &&
            IsHelper::datetime($this->payment_end_datetime)
        ) {
            $query->andWhere(['IN', 'order_id', (new Query())->select(['id'])->from(Order::tableName())->where(['BETWEEN', 'payment_at',
                (new DateTime($this->payment_begin_datetime))->setTime(0, 0, 0)->getTimestamp(),
                (new DateTime($this->payment_end_datetime))->setTime(23, 59, 59)->getTimestamp()])
            ]);
        }
        $query->andWhere("extend->'$.names' LIKE :L", [':L' => "%{$this->extend}%"]);

        return $dataProvider;
    }

}

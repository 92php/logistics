<?php

namespace app\modules\admin\modules\g\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use yii\db\Query;

/**
 * OrderItemSearch represents the model behind the search form of `app\modules\admin\modules\g\models\OrderItem`.
 */
class OrderItemSearch extends OrderItem
{

    public $order_number;

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['product_id', 'vendor_id', 'ignored'], 'integer'],
            [['order_number', 'sku', 'product_name'], 'trim'],
            [['order_number', 'sku', 'product_name'], 'string'],
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
     */
    public function search($params)
    {
        $query = OrderItem::find();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => [
                    'id' => SORT_DESC,
                ],
            ],
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'sku' => $this->sku,
            'product_id' => $this->product_id,
            'vendor_id' => $this->vendor_id,
            'ignored' => $this->ignored,
        ]);

        if ($this->order_number) {
            $query->andWhere(['IN', 'order_id', (new Query())
                ->select(['id'])
                ->from(Order::tableName())
                ->where(['number' => $this->order_number])
            ]);
        }

        $query->andFilterWhere(['like', 'product_name', $this->product_name]);

        return $dataProvider;
    }

    public function attributeLabels()
    {
        return array_merge(parent::attributeLabels(), [
            'order_number' => '订单号',
        ]);
    }

}

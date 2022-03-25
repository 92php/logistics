<?php

namespace app\modules\api\modules\g\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * OrderItemSearch represents the model behind the search form of `app\modules\admin\modules\g\models\OrderItem`.
 */
class OrderItemSearch extends OrderItem
{

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'order_id', 'product_id', 'quantity', 'vendor_id'], 'integer'],
            [['sku', 'product_name', 'extend', 'remark'], 'safe'],
            [['sale_price', 'cost_price'], 'number'],
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

        $this->load($params, '');

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'id' => $this->id,
            'order_id' => $this->order_id,
            'product_id' => $this->product_id,
            'quantity' => $this->quantity,
            'vendor_id' => $this->vendor_id,
            'sale_price' => $this->sale_price,
            'cost_price' => $this->cost_price,
        ]);

        $query->andFilterWhere(['like', 'sku', $this->sku])
            ->andFilterWhere(['like', 'product_name', $this->product_name])
            ->andFilterWhere(['like', 'remark', $this->remark]);
        $query->andWhere("extend->'$.*' LIKE :L", [':L' => "%{$this->extend}%"]);

        return $dataProvider;
    }

}

<?php

namespace app\modules\api\modules\g\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * OrderSearch represents the model behind the search form of `app\modules\admin\modules\g\models\Order`.
 */
class OrderSearch extends Order
{

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'status', 'platform_id', 'shop_id', 'product_type', 'place_order_at', 'payment_at', 'country_id'], 'integer'],
            [['number', 'consignee_name', 'consignee_mobile_phone', 'consignee_tel', 'consignee_state', 'consignee_city', 'consignee_address1', 'consignee_address2', 'consignee_postcode', 'remark'], 'safe'],
            [['total_amount'], 'number'],
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
        $query = Order::find();

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
            'total_amount' => $this->total_amount,
            'status' => $this->status,
            'platform_id' => $this->platform_id,
            'shop_id' => $this->shop_id,
            'product_type' => $this->product_type,
            'place_order_at' => $this->place_order_at,
            'payment_at' => $this->payment_at
        ]);

        $query->andFilterWhere(['like', 'number', $this->number])
            ->andFilterWhere(['like', 'consignee_name', $this->consignee_name])
            ->andFilterWhere(['like', 'consignee_mobile_phone', $this->consignee_mobile_phone])
            ->andFilterWhere(['like', 'consignee_tel', $this->consignee_tel])
            ->andFilterWhere(['like', 'country_id', $this->country_id])
            ->andFilterWhere(['like', 'consignee_state', $this->consignee_state])
            ->andFilterWhere(['like', 'consignee_city', $this->consignee_city])
            ->andFilterWhere(['like', 'consignee_address1', $this->consignee_address1])
            ->andFilterWhere(['like', 'consignee_address2', $this->consignee_address2])
            ->andFilterWhere(['like', 'consignee_postcode', $this->consignee_postcode])
            ->andFilterWhere(['like', 'remark', $this->remark]);

        return $dataProvider;
    }

}

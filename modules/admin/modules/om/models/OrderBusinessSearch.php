<?php

namespace app\modules\admin\modules\om\models;

use app\modules\api\modules\om\models\OrderBusiness;
use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * OrderBusinessSearch represents the model behind the search form of `app\modules\admin\modules\om\models\OrderBusiness`.
 */
class OrderBusinessSearch extends OrderBusiness
{

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'order_item_id', 'status'], 'integer'],
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
        $query = OrderBusiness::find();

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
            'id' => $this->id,
            'order_item_id' => $this->order_item_id,
            'status' => $this->status,
        ]);

        return $dataProvider;
    }

}

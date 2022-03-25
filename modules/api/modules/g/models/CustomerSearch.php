<?php

namespace app\modules\api\modules\g\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;

class CustomerSearch extends Customer
{

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['platform_id', 'status'], 'integer'],
            [['email', 'first_name', 'last_name', 'phone'], 'trim'],
            [['email', 'first_name', 'last_name', 'phone'], 'string'],
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
        $query = Customer::find();

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
            'platform_id' => $this->platform_id,
            'status' => $this->status,
        ]);

        $query->andFilterWhere(['like', 'email', $this->email])
            ->andFilterWhere(['like', 'first_name', $this->first_name])
            ->andFilterWhere(['like', 'last_name', $this->last_name])
            ->andFilterWhere(['like', 'phone', $this->phone]);

        return $dataProvider;
    }

}

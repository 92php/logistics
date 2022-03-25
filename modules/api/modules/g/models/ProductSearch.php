<?php

namespace app\modules\api\modules\g\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;

class ProductSearch extends Product
{

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['category_id', 'type', 'sale_method', 'status'], 'integer'],
            [['sku', 'chinese_name'], 'safe'],
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
        $query = Product::find();

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

        $query->andFilterWhere([
            'category_id' => $this->category_id,
            'type' => $this->type,
            'sale_method' => $this->sale_method,
            'status' => $this->status
        ]);

        // grid filtering conditions
        $query->andFilterWhere(['like', 'sku', $this->sku])
            ->andFilterWhere(['like', 'chinese_name', $this->chinese_name]);

        return $dataProvider;
    }

}

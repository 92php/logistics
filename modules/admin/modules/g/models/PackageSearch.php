<?php

namespace app\modules\admin\modules\g\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * PackageSearch represents the model behind the search form of `app\modules\admin\modules\g\models\Package`.
 */
class PackageSearch extends Package
{

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'country_id', 'delivery_datetime', 'shop_id', 'status', 'weight_datetime'], 'integer'],
            [['number', 'waybill_number'], 'trim'],
            [['number', 'waybill_number'], 'string'],
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
        $query = Package::find();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => [
                    'delivery_datetime' => SORT_DESC,
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
            'number' => $this->number,
            'waybill_number' => $this->waybill_number,
            'country_id' => $this->country_id,
            'delivery_datetime' => $this->delivery_datetime,
            'weight_datetime' => $this->weight_datetime,
            'logistics_line_id' => $this->logistics_line_id,
            'shop_id' => $this->shop_id,
            'status' => $this->status,
        ]);

        return $dataProvider;
    }

}

<?php

namespace app\modules\admin\modules\wuliu\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * PackageSearch represents the model behind the search form of `app\modules\admin\modules\wuliu\models\Package`.
 */
class PackageSearch extends Package
{

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'package_id', 'line_id', 'country_id', 'weight', 'dxm_account_id', 'delivery_datetime', 'estimate_days', 'final_days', 'status', 'sync_status'], 'integer'],
            [['package_number', 'order_number', 'waybill_number', 'shop_name', 'logistics_query_raw_results', 'remark'], 'safe'],
            [['freight_cost'], 'number'],
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
            'package_id' => $this->package_id,
            'line_id' => $this->line_id,
            'country_id' => $this->country_id,
            'weight' => $this->weight,
            'freight_cost' => $this->freight_cost,
            'dxm_account_id' => $this->dxm_account_id,
            'delivery_datetime' => $this->delivery_datetime,
            'estimate_days' => $this->estimate_days,
            'final_days' => $this->final_days,
            'sync_status' => $this->sync_status,
            'status' => $this->status,
        ]);

        $query->andFilterWhere(['like', 'package_number', $this->package_number])
            ->andFilterWhere(['like', 'order_number', $this->order_number])
            ->andFilterWhere(['like', 'waybill_number', $this->waybill_number])
            ->andFilterWhere(['like', 'shop_name', $this->shop_name])
            ->andFilterWhere(['like', 'logistics_query_raw_results', $this->logistics_query_raw_results])
            ->andFilterWhere(['like', 'remark', $this->remark]);

        return $dataProvider;
    }

}

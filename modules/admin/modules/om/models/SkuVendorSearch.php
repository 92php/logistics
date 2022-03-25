<?php

namespace app\modules\admin\modules\om\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * SkuVendorSearch represents the model behind the search form of `app\modules\admin\modules\om\models\SkuVendor`.
 */
class SkuVendorSearch extends SkuVendor
{

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['vendor_id', 'enabled'], 'integer'],
            [['sku', 'remark'], 'safe'],
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
        $query = SkuVendor::find();

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
            'vendor_id' => $this->vendor_id,
            'enabled' => $this->enabled,
        ]);

        $query->andFilterWhere(['like', 'sku', $this->sku])
            ->andFilterWhere(['like', 'remark', $this->remark]);

        return $dataProvider;
    }

}

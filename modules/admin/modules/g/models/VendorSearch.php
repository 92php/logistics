<?php

namespace app\modules\admin\modules\g\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * VendorSearch represents the model behind the search form of `app\modules\admin\modules\g\models\Vendor`.
 */
class VendorSearch extends Vendor
{

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'production', 'credibility', 'enabled', 'created_at', 'created_by', 'updated_at', 'updated_by', 'member_id'], 'integer'],
            [['name', 'address', 'tel', 'linkman', 'mobile_phone', 'remark'], 'safe'],
            [['receipt_duration'], 'number'],
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
        $query = Vendor::find();

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
            'receipt_duration' => $this->receipt_duration,
            'production' => $this->production,
            'credibility' => $this->credibility,
            'enabled' => $this->enabled,
            'created_at' => $this->created_at,
            'created_by' => $this->created_by,
            'updated_at' => $this->updated_at,
            'updated_by' => $this->updated_by,
        ]);

        $query->andFilterWhere(['like', 'name', $this->name])
            ->andFilterWhere(['like', 'address', $this->address])
            ->andFilterWhere(['like', 'tel', $this->tel])
            ->andFilterWhere(['like', 'linkman', $this->linkman])
            ->andFilterWhere(['like', 'mobile_phone', $this->mobile_phone])
            ->andFilterWhere(['like', 'remark', $this->remark]);

        return $dataProvider;
    }

}

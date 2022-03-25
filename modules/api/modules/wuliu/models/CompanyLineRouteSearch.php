<?php

namespace app\modules\api\modules\wuliu\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * CompanyLineRouteSearch represents the model behind the search form of `app\modules\api\modules\wuliu\models\CompanyLineRoute`.
 */
class CompanyLineRouteSearch extends CompanyLineRoute
{

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'line_id', 'step', 'estimate_days', 'enabled'], 'integer'],
            [['event', 'detection_keyword'], 'safe'],
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
        $query = CompanyLineRoute::find();

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
            'line_id' => $this->line_id,
            'step' => $this->step,
            'estimate_days' => $this->estimate_days,
            'enabled' => $this->enabled
        ]);

        $query->andFilterWhere(['like', 'event', $this->event])
            ->andFilterWhere(['like', 'detection_keyword', $this->detection_keyword]);

        return $dataProvider;
    }

}

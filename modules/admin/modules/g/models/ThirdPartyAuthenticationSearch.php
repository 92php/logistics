<?php

namespace app\modules\admin\modules\g\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * ThirdPartyAuthenticationSearch represents the model behind the search form of `app\modules\admin\modules\g\models\ThirdPartyAuthentication`.
 */
class ThirdPartyAuthenticationSearch extends ThirdPartyAuthentication
{

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['platform_id', 'enabled'], 'integer'],
            [['name', 'remark'], 'safe'],
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
        $query = ThirdPartyAuthentication::find();

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
            'platform_id' => $this->platform_id,
            'enabled' => $this->enabled,
        ]);

        $query->andFilterWhere(['like', 'name', $this->name]);

        return $dataProvider;
    }

}

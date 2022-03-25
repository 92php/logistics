<?php

namespace app\modules\admin\modules\hub\modules\shopify\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * ShopSearch represents the model behind the search form of `app\modules\admin\modules\hub\modules\shopify\models\Shop`.
 */
class ShopSearch extends Shop
{

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['project_id', 'enabled'], 'integer'],
            [['key', 'url', 'name'], 'safe'],
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
        $query = Shop::find();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => [
                    'id' => SORT_DESC
                ]
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
            'project_id' => $this->project_id,
            'enabled' => $this->enabled,
        ]);

        $query->andFilterWhere(['like', 'key', $this->key])
            ->andFilterWhere(['like', 'url', $this->url])
            ->andFilterWhere(['like', 'name', $this->name]);

        return $dataProvider;
    }

}

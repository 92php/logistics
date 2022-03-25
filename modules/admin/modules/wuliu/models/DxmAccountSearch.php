<?php

namespace app\modules\admin\modules\wuliu\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * DxmAccountSearch represents the model behind the search form of `app\modules\admin\modules\wuliu\models\DxmAccount`.
 */
class DxmAccountSearch extends DxmAccount
{

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'company_id', 'is_valid', 'platform_id'], 'integer'],
            [['username', 'password', 'remark'], 'safe'],
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
        $query = DxmAccount::find();

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
            'is_valid' => $this->is_valid,
            'company_id' => $this->company_id,
            'platform_id' => $this->platform_id,
        ]);

        $query->andFilterWhere(['like', 'username', $this->username])
            ->andFilterWhere(['like', 'password', $this->password])
            ->andFilterWhere(['like', 'remark', $this->remark]);

        return $dataProvider;
    }

}

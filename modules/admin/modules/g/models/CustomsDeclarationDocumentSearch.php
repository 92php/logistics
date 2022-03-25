<?php

namespace app\modules\admin\modules\g\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\modules\admin\modules\g\models\CustomsDeclarationDocument;

/**
 * CustomsDeclarationDocumentSearch represents the model behind the search form of `app\modules\admin\modules\g\models\CustomsDeclarationDocument`.
 */
class CustomsDeclarationDocumentSearch extends CustomsDeclarationDocument
{

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['danger_level', 'default', 'enabled'], 'integer'],
            [['code', 'chinese_name', 'english_name'], 'safe'],
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
        $query = CustomsDeclarationDocument::find();

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
            'danger_level' => $this->danger_level,
            'default' => $this->default,
            'enabled' => $this->enabled,
        ]);

        $query->andFilterWhere(['like', 'code', $this->code])
            ->andFilterWhere(['like', 'chinese_name', $this->chinese_name])
            ->andFilterWhere(['like', 'english_name', $this->english_name]);

        return $dataProvider;
    }

}

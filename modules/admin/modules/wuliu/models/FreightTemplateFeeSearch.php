<?php

namespace app\modules\admin\modules\wuliu\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * FreightTemplateFeeSearch represents the model behind the search form of `app\modules\admin\modules\wuliu\models\FreightTemplateFee`.
 */
class FreightTemplateFeeSearch extends FreightTemplateFee
{

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'template_id', 'line_id', 'min_weight', 'max_weight', 'first_weight', 'continued_weight', 'enabled'], 'integer'],
            [['first_fee', 'continued_fee', 'base_fee'], 'number'],
            [['remark'], 'safe'],
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
        $query = FreightTemplateFee::find();

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
            'template_id' => $this->template_id,
            'line_id' => $this->line_id,
            'min_weight' => $this->min_weight,
            'max_weight' => $this->max_weight,
            'first_weight' => $this->first_weight,
            'first_fee' => $this->first_fee,
            'continued_weight' => $this->continued_weight,
            'continued_fee' => $this->continued_fee,
            'base_fee' => $this->base_fee,
            'enabled' => $this->enabled,
            'created_at' => $this->created_at,
            'created_by' => $this->created_by,
            'updated_at' => $this->updated_at,
            'updated_by' => $this->updated_by,
        ]);

        $query->andFilterWhere(['like', 'remark', $this->remark]);

        return $dataProvider;
    }

}

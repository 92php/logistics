<?php

namespace app\modules\api\modules\g\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * WarehouseSheetSearch represents the model behind the search form of `app\modules\api\modules\g\models\WarehouseSheet`.
 */
class WarehouseSheetSearch extends WarehouseSheet
{

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'warehouse_id', 'type', 'method', 'change_datetime'], 'integer'],
            [['number', 'remark'], 'safe'],
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
        $query = WarehouseSheet::find();

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
            'warehouse_id' => $this->warehouse_id,
            'type' => $this->type,
            'method' => $this->method,
            'change_datetime' => $this->change_datetime,
        ]);

        $query->andFilterWhere(['like', 'number', $this->number])
            ->andFilterWhere(['like', 'remark', $this->remark]);

        return $dataProvider;
    }

}

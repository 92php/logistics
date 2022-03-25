<?php

namespace app\modules\admin\modules\g\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\modules\admin\modules\g\models\Warehouse;

/**
 * WarehouseSearch represents the model behind the search form of `app\modules\admin\modules\g\models\Warehouse`.
 */
class WarehouseSearch extends Warehouse
{

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'enabled', 'created_at', 'created_by', 'updated_at', 'updated_by'], 'integer'],
            [['name', 'address', 'linkman', 'tel', 'remark'], 'safe'],
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
        $query = Warehouse::find();

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
            'enabled' => $this->enabled,
            'created_at' => $this->created_at,
            'created_by' => $this->created_by,
            'updated_at' => $this->updated_at,
            'updated_by' => $this->updated_by,
        ]);

        $query->andFilterWhere(['like', 'name', $this->name])
            ->andFilterWhere(['like', 'address', $this->address])
            ->andFilterWhere(['like', 'linkman', $this->linkman])
            ->andFilterWhere(['like', 'tel', $this->tel])
            ->andFilterWhere(['like', 'remark', $this->remark]);

        return $dataProvider;
    }

}

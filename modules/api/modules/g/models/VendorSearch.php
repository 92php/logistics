<?php

namespace app\modules\api\modules\g\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use yii\data\Pagination;

/**
 * VendorSearch represents the model behind the search form of `app\modules\api\modules\g\models\Vendor`.
 */
class VendorSearch extends Vendor
{

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['enabled'], 'integer'],
            [['name', 'tel', 'linkman', 'mobile_phone', 'remark'], 'safe'],
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
            'pagination' => new Pagination([
                'pageSizeLimit' => [1, 50],
                'defaultPageSize' => 50
            ])
        ]);

        $this->load($params, '');

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'enabled' => $this->enabled,
        ]);

        $query->andFilterWhere(['like', 'name', $this->name])
            ->andFilterWhere(['like', 'tel', $this->tel])
            ->andFilterWhere(['like', 'linkman', $this->linkman])
            ->andFilterWhere(['like', 'mobile_phone', $this->mobile_phone])
            ->andFilterWhere(['like', 'remark', $this->remark]);

        return $dataProvider;
    }

}

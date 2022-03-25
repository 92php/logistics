<?php

namespace app\modules\admin\modules\wuliu\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\modules\admin\modules\wuliu\models\AmazonOrderItem;

/**
 * AmazonOrderItemSearch represents the model behind the search form of `app\modules\admin\modules\wuliu\models\AmazonOrderItem`.
 */
class AmazonOrderItemSearch extends AmazonOrderItem
{

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'product_quantity'], 'integer'],
            [['product_name', 'product_image', 'size', 'color', 'customized', 'order_id'], 'safe'],
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
        $query = AmazonOrderItem::find();

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
            'order_id' => $this->order_id,
            'product_quantity' => $this->product_quantity,
            'created_at' => $this->created_at,
            'created_by' => $this->created_by,
            'updated_at' => $this->updated_at,
            'updated_by' => $this->updated_by,
        ]);

        $query->andFilterWhere(['like', 'product_name', $this->product_name])
            ->andFilterWhere(['like', 'product_image', $this->product_image])
            ->andFilterWhere(['like', 'size', $this->size])
            ->andFilterWhere(['like', 'color', $this->color])
            ->andFilterWhere(['like', 'customized', $this->customized]);

        return $dataProvider;
    }

}

<?php

namespace app\modules\admin\modules\om\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use yii\db\Query;

/**
 * OrderItemRouteCancelLogSearch represents the model behind the search form of `app\modules\admin\modules\om\models\OrderItemRouteCancelLog`.
 */
class OrderItemRouteCancelLogSearch extends OrderItemRouteCancelLog
{

    /**
     * @var $sku string SKU
     */
    public $sku;

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'order_item_route_id', 'canceled_by', 'confirmed_status', 'confirmed_by'], 'integer'],
            [['canceled_reason', 'confirmed_message', 'sku'], 'safe'],
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
        $query = OrderItemRouteCancelLog::find();

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
            'order_item_route_id' => $this->order_item_route_id,
            'canceled_by' => $this->canceled_by,
            'confirmed_status' => $this->confirmed_status,
            'confirmed_by' => $this->confirmed_by,
        ]);

        $query->andFilterWhere(['like', 'canceled_reason', $this->canceled_reason])
            ->andFilterWhere(['like', 'confirmed_message', $this->confirmed_message]);

        if ($this->sku) {
            $query->andFilterWhere(['IN', 'order_item_route_id', (new Query())->select(['id'])->from('{{%om_order_item_route}}')->where(['IN', 'order_item_id', (new Query())->select(['id'])->from('{{%g_order_item}}')->where(['LIKE', 'sku', $this->sku])])]);
        }

        return $dataProvider;
    }

}

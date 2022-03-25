<?php

namespace app\modules\admin\modules\om\models;

use app\modules\admin\modules\g\models\OrderItem;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use yii\db\Query;

/**
 * OrderRouteSearch represents the model behind the search form of `app\modules\admin\modules\om\models\OrderRoute`.
 */
class OrderItemRouteSearch extends OrderItemRoute
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
            [['id', 'order_item_id', 'place_order_at', 'place_order_by', 'vendor_id', 'receipt_at', 'receipt_status', 'production_at', 'vendor_deliver_at', 'receiving_at', 'inspection_at', 'warehousing_at', 'inspection_number', 'is_reissue', 'is_accord_with', 'is_information_match', 'status', 'parent_id'], 'integer'],
            [['reason', 'feedback', 'information_feedback', 'sku'], 'safe'],
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
        $query = OrderItemRoute::find();

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
            'order_item_id' => $this->order_item_id,
            'place_order_at' => $this->place_order_at,
            'place_order_by' => $this->place_order_by,
            'vendor_id' => $this->vendor_id,
            'parent_id' => $this->parent_id,
            'receipt_at' => $this->receipt_at,
            'receipt_status' => $this->receipt_status,
            'production_at' => $this->production_at,
            'vendor_deliver_at' => $this->vendor_deliver_at,
            'receiving_at' => $this->receiving_at,
            'inspection_at' => $this->inspection_at,
            'warehousing_at' => $this->warehousing_at,
            'inspection_number' => $this->inspection_number,
            'is_reissue' => $this->is_reissue,
            'is_accord_with' => $this->is_accord_with,
            'is_information_match' => $this->is_information_match,
        ]);
        if ($this->sku) {
            $query->andFilterWhere(['IN', 'order_item_id', (new Query())->select(['id'])->from(OrderItem::tableName())->where(['LIKE', 'sku', $this->sku])]);
        }
        $query->andFilterWhere(['like', 'reason', $this->reason])
            ->andFilterWhere(['like', 'feedback', $this->feedback])
            ->andFilterWhere(['like', 'information_feedback', $this->information_feedback]);

        return $dataProvider;
    }

}

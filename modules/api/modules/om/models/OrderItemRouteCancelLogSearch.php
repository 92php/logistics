<?php

namespace app\modules\api\modules\om\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * OrderItemRouteCancelLogSearch represents the model behind the search form of `app\modules\api\modules\om\models\OrderItemRouteCancelLog`.
 */
class OrderItemRouteCancelLogSearch extends OrderItemRouteCancelLog
{

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'order_item_route_id', 'canceled_by', 'confirmed_status', 'confirmed_by'], 'integer'],
            [['canceled_reason', 'confirmed_message'], 'safe'],
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
        $query = OrderItemRouteCancelLog::find()
            ->leftJoin("{{%om_order_item_route}} r", 'r.id = order_item_route_id')
            ->leftJoin("{{%g_vendor}} v", 'v.id = r.vendor_id')
            ->leftJoin("{{%g_vendor_member}} vm", 'vm.vendor_id = v.id')
            ->where(['vm.member_id' => Yii::$app->getUser()->getId()]);

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
            'order_item_route_id' => $this->order_item_route_id,
            'canceled_by' => $this->canceled_by,
            'confirmed_status' => $this->confirmed_status,
            'confirmed_by' => $this->confirmed_by,
        ]);

        $query->andFilterWhere(['like', 'canceled_reason', $this->canceled_reason])
            ->andFilterWhere(['like', 'confirmed_message', $this->confirmed_message]);

        return $dataProvider;
    }

}

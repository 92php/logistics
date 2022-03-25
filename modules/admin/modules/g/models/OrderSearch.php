<?php

namespace app\modules\admin\modules\g\models;

use DateTime;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use yii\db\Query;

/**
 * OrderSearch represents the model behind the search form of `app\modules\admin\modules\g\models\Order`.
 */
class OrderSearch extends Order
{

    /**
     * @var int 组织
     */
    public $organization_id;

    /**
     * @var string 下单开始时间
     */
    public $begin_date;

    /**
     * @var string 下单结束时间
     */
    public $end_date;

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['type', 'country_id', 'status', 'third_party_platform_id', 'platform_id', 'shop_id', 'product_type', 'organization_id'], 'integer'],
            [['begin_date', 'end_date'], 'date', 'format' => 'php:Y-m-d'],
            [['number'], 'trim'],
            [['number'], 'string'],
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
     * @throws \Exception
     */
    public function search($params)
    {
        $query = Order::find();

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
            'type' => $this->type,
            'country_id' => $this->country_id,
            'status' => $this->status,
            'platform_id' => $this->platform_id,
            'shop_id' => $this->shop_id,
            'product_type' => $this->product_type,
            'third_party_platform_id' => $this->third_party_platform_id,
        ]);

        if ($this->begin_date && $this->end_date) {
            try {
                $beginDate = (new DateTime($this->begin_date));
                $endDate = (new DateTime($this->end_date));
            } catch (\Exception $e) {
                $beginDate = new DateTime();
                $endDate = new DateTime();
            }
            $query->andWhere(['BETWEEN', 'place_order_at', $beginDate->setTime(0, 0, 0)->getTimestamp(), $endDate->setTime(23, 59, 59)->getTimestamp()]);
        }

        if ($this->organization_id) {
            $query->andWhere(['IN', 'shop_id', (new Query())
                ->select(['id'])
                ->from('{{%g_shop}}')
                ->where(['organization_id' => $this->organization_id])
            ]);
        }

        $query->andFilterWhere(['like', 'number', $this->number]);

        return $dataProvider;
    }

    public function attributeLabels()
    {
        return array_merge(parent::attributeLabels(), [
            'organization_id' => '组织',
            'begin_date' => '下单开始时间',
            'end_date' => '下单结束时间',
        ]);
    }

}

<?php

namespace app\modules\api\modules\g\models;

use DateTime;
use yadjet\helpers\DatetimeHelper;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use yii\db\Query;

/**
 * PackageSearch represents the model behind the search form of `app\modules\api\modules\g\models\Package`.
 */
class PackageSearch extends Package
{

    /**
     * @var int 组织
     */
    public $organization_id;

    /**
     * @var string 订单号
     */
    public $order_number;

    /**
     * @var string|null 订单下单开始时间
     */
    public $order_begin_place_date;

    /**
     * @var string|null 订单下单结束时间
     */
    public $order_end_place_date;

    /**
     * @var string|null 订单付款开始时间
     */
    public $order_begin_payment_date;

    /**
     * @var string|null 订单付款结束时间
     */
    public $order_end_payment_date;

    /**
     * @var int|null 剩余发货天数
     */
    public $remaining_delivery_days;

    /**
     * @var string 发货开始时间
     */
    public $begin_date;

    /**
     * @var string 发货结束时间
     */
    public $end_date;

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'organization_id', 'country_id', 'logistics_line_id', 'status', 'remaining_delivery_days'], 'integer'],
            [['number', 'waybill_number', 'order_number'], 'trim'],
            [['number', 'waybill_number', 'order_number'], 'string'],
            [['begin_date', 'end_date'], 'date', 'format' => 'php:Y-m-d'],
            ['order_number', 'filter', 'filter' => function () {
                $number = $this->order_number;
                $numbers = preg_split("/\s*[, ]\s*/", trim($number), -1, PREG_SPLIT_NO_EMPTY);
                $numbers = array_unique(array_filter($numbers));

                return implode(',', $numbers);
            }, 'when' => function ($model) {
                return $model->order_number;
            }],
            [['order_begin_place_date', 'order_end_place_date', 'order_begin_payment_date', 'order_end_payment_date'], 'date', 'format' => 'php:Y-m-d'],
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
        $query = Package::find();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => [
                    'delivery_datetime' => SORT_DESC,
                ],
            ],
        ]);

        $this->load($params, '');

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        if ($this->organization_id) {
            $query->andWhere(['IN', 'shop_id', (new Query())
                ->select(['id'])
                ->from(Shop::tableName())
                ->where(['organization_id' => $this->organization_id])
            ]);
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'number' => $this->number,
            'waybill_number' => $this->waybill_number,
            'country_id' => $this->country_id,
            'logistics_line_id' => $this->logistics_line_id,
            'status' => $this->status,
        ]);

        if ($this->begin_date && $this->end_date) {
            try {
                $query->andWhere([
                    'BETWEEN',
                    'delivery_datetime',
                    (new DateTime($this->begin_date))->setTime(0, 0, 0)->getTimestamp(),
                    (new DateTime($this->end_date))->setTime(23, 59, 59)->getTimestamp(),
                ]);
            } catch (\Exception $e) {
                $query->where('0 = 1');
            }
        }

        $orderQueryCondition = [];
        if ($this->order_number) {
            $orderQueryCondition = ['AND', $orderQueryCondition, ['number' => explode(',', $this->order_number)]];
        }
        if ($this->order_begin_place_date && $this->order_end_place_date) {
            try {
                $beginDate = (new DateTime($this->order_begin_place_date))->setTime(0, 0, 0);
                $endDate = (new DateTime($this->order_end_place_date))->setTime(23, 59, 59);
                $orderQueryCondition = ['AND', $orderQueryCondition, ['BETWEEN', 'place_order_at', $beginDate->getTimestamp(), $endDate->getTimestamp()]];
            } catch (\Exception $e) {
                $orderQueryCondition = null;
            }
        }
        if ($orderQueryCondition !== null && $this->order_begin_payment_date && $this->order_end_payment_date) {
            try {
                $beginDate = (new DateTime($this->order_begin_payment_date))->setTime(0, 0, 0);
                $endDate = (new DateTime($this->order_end_payment_date))->setTime(23, 59, 59);
                $orderQueryCondition = ['AND', $orderQueryCondition, ['BETWEEN', 'payment_at', $beginDate->getTimestamp(), $endDate->getTimestamp()]];
            } catch (\Exception $e) {
                $orderQueryCondition = null;
            }
        }
        if ($orderQueryCondition === null) {
            $query->where('0 = 1');
        } elseif ($orderQueryCondition) {
            $query->andWhere(['IN', 'id', (new Query())
                ->select('package_id')
                ->from('{{%g_package_order_item}}')
                ->where(['IN', 'order_id', (new Query())
                    ->select(['id'])
                    ->from('{{%g_order}}')
                    ->where($orderQueryCondition)
                ])
            ]);
        }

        /**
         * 发货剩余天数处理
         * 0: 到期
         * 1: 剩余 1 天
         * 2: 剩余 2 天
         * 3: 剩余 3 天
         * 4: 剩余 4 天
         * 5: 剩余 5 天
         * 6: 大于 5 天
         */
        if ($this->remaining_delivery_days !== null) {
            $maxDays = 10;
            if ($this->remaining_delivery_days == 0) {
                $this->remaining_delivery_days = $maxDays;
            } elseif ($this->remaining_delivery_days > 5) {
                $this->remaining_delivery_days = 6;
            }
            $datetime = (new DateTime())->setTime(0, 0, 0);
            $datetime->modify("-{$this->remaining_delivery_days} days");
            if ($this->remaining_delivery_days == $maxDays) {
                $query->andWhere('[[delivery_datetime]] <= :ts', [':ts' => $datetime->getTimestamp() - $this->remaining_delivery_days * 86400]);
            } else {
                if ($this->remaining_delivery_days == 6) {
                    // 大于五天
                    $n1 = $datetime->getTimestamp();
                    $n2 = $datetime->setTime(23, 59, 59)->getTimestamp();
                } else {
                    $n1 = $datetime->getTimestamp();
                    $n2 = $datetime->setTime(23, 59, 59)->getTimestamp();
                }
                $query->andWhere('[[delivery_datetime]] BETWEEN :begin AND :end', [
                    ':begin' => $n1,
                    ':end' => $n2,
                ]);
            }
        }

        return $dataProvider;
    }

    public function attributeLabels()
    {
        return array_merge(parent::attributeLabels(), [
            'order_begin_place_date' => '订单下单开始时间',
            'order_end_place_date' => '订单下单结束时间',
            'order_begin_payment_date' => '订单付款开始时间',
            'order_end_payment_date' => '订单付款结束时间',
            'remaining_delivery_days' => '剩余发货天数',
        ]);
    }

}


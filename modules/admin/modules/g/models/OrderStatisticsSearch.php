<?php

namespace app\modules\admin\modules\g\models;

use app\extensions\data\ArrayDataProvider;
use DateTime;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use yii\db\Query;

/**
 * OrderStatisticsSearch represents the model behind the search form of `app\modules\admin\modules\g\models\Order`.
 */
class OrderStatisticsSearch extends Order
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
            [['organization_id'], 'integer'],
            [['begin_date', 'end_date'], 'date', 'format' => 'php:Y-m-d'],
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
     * @return ArrayDataProvider|ActiveDataProvider
     * @throws \Exception
     */
    public function search($params)
    {
        $this->load($params);
        $condition = [];
        if ($this->organization_id) {
            $condition = ['IN', 'shop_id', (new Query())
                ->select(['id'])
                ->from('{{%g_shop}}')
                ->where(['organization_id' => intval($this->organization_id)])
            ];
        }
        try {
            $beginDate = new DateTime($this->begin_date);
            $endDate = new DateTime($this->end_date);
        } catch (\Exception $e) {
            $beginDate = new DateTime();
            $endDate = new DateTime();
        }
        $beginDate->setTime(0, 0, 0);
        $endDate->setTime(23, 59, 59);
        $condition = ['AND', $condition, ['BETWEEN', 'place_order_at', $beginDate->getTimestamp(), $endDate->getTimestamp()]];

        $query = (new Query())
            ->select(['s.organization_id', 't.shop_id', 's.name AS shop_name', 'COUNT(*) AS count'])
            ->from('{{%g_order}} t')
            ->leftJoin('{{%g_shop}} s', '[[t.shop_id]] = [[s.id]]')
            ->where($condition)
            ->groupBy('s.organization_id, t.shop_id')
            ->orderBy(['s.organization_id' => SORT_ASC]);

        return new ArrayDataProvider([
            'models' => $query->all(),
        ]);
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

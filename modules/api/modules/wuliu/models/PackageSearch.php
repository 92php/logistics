<?php

namespace app\modules\api\modules\wuliu\models;

use app\modules\api\modules\g\models\Shop;
use DateTime;
use yadjet\helpers\IsHelper;
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
     * @var string 发货开始日期
     */
    public $delivery_begin_datetime;

    /**
     * @var string 发货结束日期
     */
    public $delivery_end_datetime;

    /**
     * @var integer 物流公司id
     */
    public $company_id;

    /**
     * @var string 店铺名
     */
    public $shop_name;

    /**
     * @var string 订单号
     */
    public $order_number;

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'organization_id', 'weight', 'delivery_datetime', 'logistics_line_id', 'logistics_last_check_datetime', 'status', 'created_at', 'created_by', 'updated_at', 'updated_by', 'company_id', 'country_id'], 'integer'],
            [['key', 'number', 'waybill_number', 'logistics_query_raw_results', 'remark', 'delivery_begin_datetime', 'delivery_end_datetime', 'order_number', 'shop_name'], 'safe'],
            [['freight_cost'], 'number'],
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
            'id' => $this->id,
            'weight' => $this->weight,
            'freight_cost' => $this->freight_cost,
            'delivery_datetime' => $this->delivery_datetime,
            'logistics_line_id' => $this->logistics_line_id,
            'logistics_last_check_datetime' => $this->logistics_last_check_datetime,
            'country_id' => $this->country_id,
            'created_at' => $this->created_at,
            'created_by' => $this->created_by,
            'updated_at' => $this->updated_at,
            'updated_by' => $this->updated_by,
        ]);

        $query->andFilterWhere(['like', 'key', $this->key])
            ->andFilterWhere(['like', 'number', $this->number])
            ->andFilterWhere(['like', 'waybill_number', $this->waybill_number])
            ->andFilterWhere(['like', 'logistics_query_raw_results', $this->logistics_query_raw_results])
            ->andFilterWhere(['like', 'remark', $this->remark]);

        // 0 表示待发货, 100 表示已发货
        if ($this->status == 100) {
            $query->andWhere("status > 0");
        } else {
            $query->andFilterWhere([
                'status' => $this->status,
            ]);
        }

        if ($this->company_id) {
            $query->andWhere(['IN', 'logistics_line_id', (new Query())
                ->select(['id'])
                ->from('{{%wuliu_company_line}}')
                ->where(['company_id' => $this->company_id])
            ]);
        }

        if ($this->shop_name) {
            $query->andWhere(['IN', 'id', (new Query())->select(['p.id'])->from("{{%g_package}} p")
                ->innerJoin("{{%g_package_order_item}} poi", "poi.package_id = p.id")
                ->innerJoin("{{%g_order}} o", "o.id = poi.order_id")
                ->innerJoin("{{%g_shop}} s", "o.shop_id = s.id")
                ->where(['s.name' => $this->shop_name])
            ]);
        }

        if ($this->order_number) {
            $query->andWhere(['IN', 'id', (new Query())->select(['p.id'])->from("{{%g_package}} p")
                ->innerJoin("{{%g_package_order_item}} poi", "poi.package_id = p.id")
                ->innerJoin("{{%g_order}} o", "o.id = poi.order_id")
                ->where(['o.number' => $this->order_number])
            ]);
        }

        if (
            $this->delivery_end_datetime &&
            $this->delivery_begin_datetime &&
            IsHelper::datetime($this->delivery_begin_datetime) &&
            IsHelper::datetime($this->delivery_end_datetime)
        ) {
            $query->andWhere(['BETWEEN', 'delivery_datetime',
                (new DateTime($this->delivery_begin_datetime))->setTime(0, 0, 0)->getTimestamp(),
                (new DateTime($this->delivery_end_datetime))->setTime(23, 59, 59)->getTimestamp()]);
        }

        return $dataProvider;
    }

}


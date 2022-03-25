<?php

namespace app\modules\admin\modules\g\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use yii\db\Query;

/**
 * SyncTaskSearch represents the model behind the search form of `app\modules\admin\modules\g\models\SyncTask`.
 */
class SyncTaskSearch extends SyncTask
{

    /**
     * @var integer 组织
     */
    public $organization_id;

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['organization_id', 'shop_id', 'status'], 'integer'],
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
        $query = SyncTask::find();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 100
            ],
            'sort' => [
                'defaultOrder' => [
                    'start_datetime' => SORT_DESC,
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
            'shop_id' => $this->shop_id,
            'status' => $this->status,
        ]);
        if ($this->organization_id) {
            $query->andWhere(['IN', 'shop_id', (new Query())
                ->select(['id'])
                ->from(ShopSearch::tableName())
                ->where(['organization_id' => $this->organization_id])
            ]);
        }

        return $dataProvider;
    }

    public function attributeLabels()
    {
        return array_merge(parent::attributeLabels(), [
            'organization_id' => '组织',
        ]);
    }

}

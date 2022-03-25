<?php

namespace app\modules\admin\modules\g\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use yii\db\Query;

/**
 * ShopSearch represents the model behind the search form of `app\modules\admin\modules\g\models\Shop`.
 */
class ShopSearch extends Shop
{

    public $third_party_platform_id;

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'organization_id', 'platform_id', 'enabled', 'third_party_platform_id'], 'integer'],
            [['name', 'third_party_sign'], 'trim'],
            [['name', 'third_party_sign'], 'string'],
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
        $query = Shop::find();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 200,
            ],
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
            'organization_id' => $this->organization_id,
            'platform_id' => $this->platform_id,
            'enabled' => $this->enabled,
        ]);

        if ($this->third_party_platform_id) {
            $query->andWhere(['IN', 'third_party_authentication_id', (new Query())
                ->select(['id'])
                ->from(ThirdPartyAuthentication::tableName())
                ->where(['platform_id' => $this->third_party_platform_id])
            ]);
        }

        $query->andFilterWhere(['like', 'name', $this->name])
            ->andFilterWhere(['like', 'third_party_sign', $this->third_party_sign]);

        return $dataProvider;
    }

    public function attributeLabels()
    {
        return array_merge(parent::attributeLabels(), [
            'third_party_platform_id' => '第三方平台',
        ]);
    }

}

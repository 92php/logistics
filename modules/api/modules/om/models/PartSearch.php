<?php

namespace app\modules\api\modules\om\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use function var_dump;

/**
 * Class PartSearch
 *
 * @package app\modules\api\modules\om\models
 */
class PartSearch extends Part
{

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'is_empty', 'created_at', 'created_by', 'updated_at', 'updated_by', 'sn'], 'integer'],
            [['sku', 'customized'], 'safe'],
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
        $query = Part::find()->leftJoin("{{%g_vendor}} v", 'v.id = vendor_id')->where(['v.member_id' => Yii::$app->getUser()->getId()]);

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSizeLimit' => false,
                'defaultPageSize' => 500,
            ]
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
            'sn' => $this->sn,
            'is_empty' => $this->is_empty,
            'created_at' => $this->created_at,
            'created_by' => $this->created_by,
            'updated_at' => $this->updated_at,
            'updated_by' => $this->updated_by,
        ]);

        $query->andFilterWhere(['like', 'sku', $this->sku])
            ->andFilterWhere(['like', 'customized', $this->customized]);

        return $dataProvider;
    }
}

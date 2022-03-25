<?php

namespace app\modules\api\modules\om\models;

use app\extensions\data\ArrayDataProvider;
use Yii;
use yii\base\Model;
use yii\helpers\ArrayHelper;

class SkuVendorSearch extends SkuVendor
{

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['vendor_id'], 'integer'],
            [['sku'], 'safe'],
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
     * @return ArrayDataProvider
     * @throws \yii\db\Exception
     */
    public function search($params)
    {
        $this->load($params, '');
        $items = [];
        $sql = "SELECT [[t.id]], [[t.ordering]], [[t.sku]], [[t.vendor_id]], [[t.cost_price]], [[t.production_min_days]], [[t.production_max_days]], [[t.enabled]], [[t.remark]], [[vendor.name]] AS [[vendor_name]] FROM {{%om_sku_vendor}} t LEFT JOIN {{%g_vendor}} vendor ON [[t.vendor_id]] = [[vendor.id]]";
        $condition = [];
        $params = [];
        $sku = trim($this->sku);
        if ($sku) {
            $condition[] = '[[sku]] = :sku';
            $params[':sku'] = $sku;
        }
        if ($this->vendor_id) {
            $condition[] = '[[vendor_id]] = :vendorId';
            $params[':vendorId'] = $this->vendor_id;
        }
        if ($condition) {
            $sql .= " WHERE " . implode(' AND ', $condition);
        }
        $rawItems = Yii::$app->getDb()->createCommand("$sql ORDER BY [[id]] DESC", $params)->queryAll();
        foreach ($rawItems as $item) {
            if (!isset($items[$item['sku']])) {
                $items[$item['sku']] = [
                    'id' => (int) $item['id'],
                    'sku' => $item['sku'],
                    'items' => []
                ];
            }
            $items[$item['sku']]['items'][] = [
                'id' => (int) $item['id'],
                'ordering' => (int) $item['ordering'],
                'vendor_id' => (int) $item['vendor_id'],
                'vendor_name' => $item['vendor_name'],
                'cost_price' => (float) $item['cost_price'],
                'production_min_days' => (int) $item['production_min_days'],
                'production_max_days' => (int) $item['production_max_days'],
                'enabled' => boolval($item['enabled']),
                'remark' => $item['remark'],
            ];
        }
        if ($items) {
            foreach ($items as $key => $item) {
                $t = $item['items'];
                isset($t[1]) && ArrayHelper::multisort($t, 'ordering', SORT_ASC, SORT_NUMERIC);
                $items[$key]['items'] = $t;
            }
        }

        return new ArrayDataProvider([
            'key' => 'id',
            'allModels' => $items,
        ]);
    }

}
<?php

namespace app\modules\api\modules\om\forms;

use app\modules\api\modules\om\models\OrderItemRoute;
use Yii;
use yii\base\Model;

/**
 * Class BatchReceiptForm
 * 仓库批量收货接口
 *
 * @package app\modules\api\modules\om\forms
 */
class BatchReceiptForm extends Model
{

    /**
     * 运单号
     *
     * @var string
     */
    public $waybill_number;

    public function rules()
    {
        return [
            ['waybill_number', 'required'],
            ['waybill_number', 'string'],
            ['waybill_number', function ($attribute, $params) {
                $db = Yii::$app->getDb();
                $waybillNumbers = explode(',', $this->waybill_number);
                foreach ($waybillNumbers as $waybillNumber) {
                    $exists = $db->createCommand("SELECT COUNT(*) FROM {{%om_order_item_route}} WHERE [[waybill_number]] = :waybillNumber", [':waybillNumber' => $waybillNumber])->queryScalar();
                    if (!$exists) {
                        $this->addError($attribute, '未找到此运单号，请检查。');
                    }
                }
            }]
        ];
    }

    /**
     * 保存
     *
     * @return bool
     * @throws \Throwable
     */
    public function save()
    {
        $transaction = Yii::$app->getDb()->beginTransaction();
        try {
            $waybillNumbers = explode(',', $this->waybill_number);
            foreach ($waybillNumbers as $waybillNumber) {
                //修改路由收货时间和收货状态
                $model = OrderItemRoute::find()->where(['waybill_number' => $waybillNumber])->one();
                $payload = [
                    'receipt_at' => time(),
                    'receiving_status' => OrderItemRoute::STATUS_RECEIVING_TRUE,
                    'current_node' => OrderItemRoute::NODE_STAY_INSPECTION // 质检
                ];
                $model->load($payload, '');
                $model->save();
            }

            $transaction->commit();

            return true;
        } catch (\Exception $e) {
            $transaction->rollBack();
            throw $e;
        } catch (\Throwable $e) {
            $transaction->rollBack();
            throw $e;
        }
    }
}
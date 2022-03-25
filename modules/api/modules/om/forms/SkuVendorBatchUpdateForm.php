<?php

namespace app\modules\api\modules\om\forms;

use app\modules\api\modules\om\models\SkuVendor;
use Yii;
use yii\base\Model;

/**
 * SKU 供应商数据批量更新
 *
 * @package app\modules\api\modules\om\forms
 */
class SkuVendorBatchUpdateForm extends Model
{

    /**
     * @var string SKU
     */
    public $sku;

    /**
     * @var array 供应商列表
     */
    public $vendors = [];

    public function rules()
    {
        return [
            [['sku', 'vendors'], 'required'],
            ['sku', 'trim'],
            ['sku', 'string', 'max' => 40],
            ['vendors', function ($attribute, $params) {
                $errorMessage = null;
                $vendors = $this->vendors;
                if (is_array($vendors)) {
                    foreach ($vendors as $vendor) {
                        if (isset($vendor['id'])) {
                            $model = SkuVendor::find()->where(['id' => $vendor['id']])->one();
                            if ($model === null) {
                                $errorMessage = '记录未找到。';
                                break;
                            }
                        } else {
                            $model = new SkuVendor();
                            $model->loadDefaultValues();
                        }

                        $payload = array_merge($vendor, ['sku' => $this->sku]);
                        $model->load($payload, '');
                        if (!$model->validate()) {
                            if ($model->hasErrors()) {
                                foreach ($model->getErrors() as $error) {
                                    $errorMessage = $error[0];
                                    break;
                                }
                            } else {
                                $errorMessage = '未知错误。';
                            }
                            break;
                        }
                    }
                } else {
                    $errorMessage = '错误的供应商数据格式。';
                }
                if ($errorMessage) {
                    $this->addError($attribute, $errorMessage);
                }
            }]
        ];
    }

    public function attributeLabels()
    {
        return [
            'sku' => 'SKU',
            'vendors' => '供应商',
        ];
    }

    /**
     * 保存
     *
     * @return bool
     * @throws \Exception
     * @throws \Throwable
     */
    public function save()
    {
        $transaction = Yii::$app->db->beginTransaction();
        try {
            foreach ($this->vendors as $vendor) {
                if (isset($vendor['id'])) {
                    $model = SkuVendor::find()->where(['id' => $vendor['id']])->one();
                } else {
                    $model = new SkuVendor();
                    $model->loadDefaultValues();
                }
                $payload = array_merge($vendor, ['sku' => $this->sku]);
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
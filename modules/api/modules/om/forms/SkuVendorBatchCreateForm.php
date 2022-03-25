<?php

namespace app\modules\api\modules\om\forms;

use app\modules\api\modules\om\models\SkuVendor;
use Yii;
use yii\base\Model;

/**
 * SKU 供应商数据批量添加
 *
 * @package app\modules\api\modules\om\forms
 */
class SkuVendorBatchCreateForm extends Model
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
                        $model = new SkuVendor();
                        $model->loadDefaultValues();
                        $model->load(array_merge($vendor, ['sku' => $this->sku]), '');
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
                $model = new SkuVendor();
                $model->loadDefaultValues();
                $model->load(array_merge($vendor, ['sku' => $this->sku]), '');
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
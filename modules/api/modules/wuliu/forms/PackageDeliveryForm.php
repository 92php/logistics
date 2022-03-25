<?php

namespace app\modules\api\modules\wuliu\forms;

use app\jobs\Package2DxmJob;
use app\modules\api\modules\wuliu\models\Package;
use Yii;
use yii\base\Model;
use yii\web\ServerErrorHttpException;

/**
 * 包裹单个发货
 *
 * @package app\modules\api\modules\wuliu\forms
 */
class PackageDeliveryForm extends Model
{

    /**
     * @var $_package Package
     */
    private $_package;

    /**
     * @var string 运单号
     */
    public $waybill_number;

    /**
     * @var int 重量
     */
    public $weight;

    public function rules()
    {
        return [
            [['waybill_number', 'weight'], 'trim'],
            [['waybill_number', 'weight'], 'required'],
            ['weight', 'integer', 'min' => 1],
            ['waybill_number', function ($attribute, $params) {
                $package = Package::find()->where(['waybill_number' => $this->waybill_number])->one();
                if ($package) {
                    $this->_package = $package;
                    if ($package['delivery_datetime']) {
                        $this->addError($attribute, '该包裹已经发货。');
                    }
                } else {
                    $this->addError($attribute, '包裹不存在。');
                }
            }],
        ];
    }

    public function attributeLabels()
    {
        return [
            'waybill_number' => '运单号',
            'weight' => '重量',
        ];
    }

    /**
     * 保存发货信息
     *
     * @return $this|Package
     * @throws ServerErrorHttpException
     */
    public function save()
    {
        if ($this->validate()) {
            $this->_package->weight = $this->weight;
            $this->_package->sync_status = Package::SYNC_PENDING;
            $this->_package->status = Package::STATUS_RECEIVED;
            $this->_package->delivery_datetime = time();

            if ($this->_package->save() === false && !$this->_package->hasErrors()) {
                throw new ServerErrorHttpException('发货失败，原因未知。');
            }

            Yii::$app->queue->push(new Package2DxmJob([
                'id' => $this->_package->id,
            ]));

            return $this->_package;
        } else {
            return $this;
        }
    }

}
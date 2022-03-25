<?php

namespace app\modules\api\modules\wuliu\forms;

use app\jobs\Package2DxmJob;
use app\modules\api\modules\wuliu\models\Package;
use Yii;
use yii\base\Model;

/**
 * 包裹称重发货
 *
 * @package app\modules\api\modules\wuliu\forms
 */
class PackageWeighDeliveryForm extends Model
{

    /**
     * @var $_package Package
     */
    private $_package;

    /**
     * @var string 包裹号
     */
    public $ticketsNum;

    /**
     * @var int 重量
     */
    public $weight;

    public function rules()
    {
        return [
            [['ticketsNum', 'weight'], 'trim'],
            [['ticketsNum', 'weight'], 'required'],
            ['weight', 'integer', 'min' => 1],
            ['ticketsNum', function ($attribute, $params) {
                $package = Package::find()->where(['package_number' => $this->ticketsNum])->one();
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
            'ticketsNum' => '包裹号',
            'weight' => '重量',
        ];
    }

    /**
     * 保存发货信息
     *
     * @return $this|Package
     */
    public function save()
    {
        if ($this->validate()) {
            $now = time();
            $payload = [
                'weight' => $this->weight,
                'sync_status' => Package::SYNC_PENDING,
                'status' => Package::STATUS_RECEIVED,
                'delivery_datetime' => $now,
                'updated_at' => $now,
                'updated_by' => Yii::$app->getUser()->getId(),
            ];
            $this->_package->load($payload, '');
            $this->_package->save();
            Yii::$app->queue->push(new Package2DxmJob([
                'id' => $this->_package->id,
            ]));

            return $this->_package;
        } else {
            return $this;
        }
    }

}
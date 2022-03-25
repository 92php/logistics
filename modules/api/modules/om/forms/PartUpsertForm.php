<?php

namespace app\modules\api\modules\om\forms;

use app\modules\api\models\Constant;
use app\modules\api\modules\om\models\OrderItem;
use app\modules\api\modules\om\models\Part;
use Yii;
use yii\base\Model;

/**
 * Class PartCreateUpdateForm
 *
 * 配件添加或修改
 *
 * @package app\modules\api\modules\om\forms
 */
class PartUpsertForm extends Model
{

    /**
     * @var string sku
     */
    public $sku;

    /**
     * @var string 定制信息
     */
    public $customized;

    public function rules()
    {
        return [
            [['customized', 'sku'], 'required'],
            [['customized', 'sku'], 'trim'],
            ['customized', 'string', 'max' => 20],
            ['sku', 'string', 'max' => 40],
            ['sku', 'exist', 'targetClass' => OrderItem::class, 'targetAttribute' => 'sku'],
            ['sku', function ($attribute, $params) {
                $db = Yii::$app->getDb();
                // 先获取供应商所有格子
                $count = $db->createCommand("SELECT COUNT(*) FROM {{%om_part}} p INNER JOIN {{%g_vendor}} v ON v.id = p.vendor_id INNER JOIN {{%g_vendor_member}} vm ON vm.vendor_id = v.id  WHERE [[vm.member_id]] = :memberId", [':memberId' => Yii::$app->getUser()->getId()])->queryScalar();
                // 超过500则抛出异常
                if ($count >= 500) {
                    $this->addError($attribute, '配件盒子已经满了。如果需要增加请先清空');
                }
            }]
        ];
    }

    /**
     * 保存
     */
    public function upsert()
    {
        // 获取当前供应商
        $vendorId = Yii::$app->getDb()->createCommand("SELECT [[vendor_id]] FROM {{%g_vendor_member}} WHERE [[member_id]] = :memberId", [':memberId' => Yii::$app->getUser()->getId()])->queryScalar();
        // 先判断是否有空闲格子，如果有则直接更改，没有则添加
        $model = Part::find()->where(['is_empty' => Constant::BOOLEAN_TRUE, 'vendor_id' => $vendorId])->orderBy('sn asc')->one();
        if ($model) {
            // 有则修改
            $model->sku = $this->sku;
            $model->customized = $this->customized;
            $model->is_empty = Constant::BOOLEAN_FALSE;
            $model->save();
        } else {
            // 否则添加, 先获取上一个编号为多少然后加1
            $sn = Yii::$app->getDb()->createCommand("SELECT [[sn]] FROM {{%om_part}} WHERE [[vendor_id]] = :vendorId ORDER BY sn desc", [':vendorId' => $vendorId])->queryScalar();
            if (!$sn) {
                $sn = 0;
            }
            $sn++;
            $model = new Part();
            $payload = [
                'sku' => $this->sku,
                'customized' => $this->customized,
                'vendor_id' => $vendorId,
                'sn' => $sn
            ];
            $model->load($payload, '');
            $model->save();
        }
        $model->refresh();

        return $model;
    }
}
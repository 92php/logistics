<?php

namespace app\modules\api\modules\om\forms;

use app\modules\api\models\Constant;
use app\modules\api\modules\om\models\OrderItem;
use Yii;
use yii\base\Model;

/**
 * Class ProductIgnoreForm
 * 产品忽略
 *
 * @package app\modules\api\modules\om\forms
 */
class ProductIgnoreForm extends Model
{

    public $order_item_id;

    public function rules()
    {
        return [
            ['order_item_id', 'required'],
            ['order_item_id', function ($attribute, $params) {
                $exists = Yii::$app->getDb()->createCommand("SELECT COUNT(*) FROM {{%g_order_item}} WHERE [[id]] = :id AND ignored = :ignored", [':id' => $this->order_item_id, ":ignored" => Constant::BOOLEAN_FALSE])->queryOne();
                if (!$exists) {
                    $this->addError($attribute, '未找到商品，或该商品已经被忽略');
                }
            }]
        ];
    }

    public function save()
    {
        $model = OrderItem::findOne(['id' => $this->order_item_id]);
        if ($model) {
            $model->ignored = Constant::BOOLEAN_TRUE;
            $model->save();

            return true;
        } else {
            return false;
        }
    }
}

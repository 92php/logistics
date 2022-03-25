<?php

namespace app\modules\api\modules\om\forms;

use app\modules\api\models\Constant;
use app\modules\api\modules\om\models\OrderItemRoute;
use Yii;
use yii\base\Model;
use yii\db\Exception;
use yii\db\Query;

/**
 * Class InProductionForm
 * 待生产产品移入生产中
 *
 * @package app\modules\api\modules\om\forms
 */
class InProductionForm extends Model
{

    /**
     * 路由id
     *
     * @var array
     */
    public $route_ids;

    public function rules()
    {
        return [
            ['route_ids', 'required'],
            ['route_ids', 'safe'],
            ['route_ids', function ($attribute, $params) {
                if (is_array($this->route_ids)) {
                    // 获取当前route是否为待生产状态，并且是否为这个供应商的产品
                    $memberId = Yii::$app->getUser()->getId();
                    $query = (new Query())->select(['r.current_node', 'v.member_id'])
                        ->from("{{%om_order_item_route}} r")
                        ->innerJoin("{{%g_vendor}} v", 'v.id = r.vendor_id')
                        ->where(['IN', 'r.id', $this->route_ids])->all();
                    if ($query) {
                        foreach ($query as $item) {
                            if ($item['current_node'] != OrderItemRoute::NODE_TO_PRODUCED) {
                                $this->addError($attribute, '商品必须处于待生产。');
                                break;
                            }
                            if ($item['member_id'] != $memberId) {
                                $this->addError($attribute, '商品不是该供应商商品，不可修改。');
                                break;
                            }
                        }
                    } else {
                        $this->addError($attribute, '未找到商品');
                    }
                } else {
                    $this->addError($attribute, '数据格式错误');
                }
            }]
        ];
    }

    /**
     * 修改数据
     *
     * @throws Exception
     */
    public function save()
    {
        Yii::$app->getDb()->createCommand()->update("{{%om_order_item_route}}", [
            'current_node' => OrderItemRoute::NODE_ALREADY_PRODUCED,
            'production_at' => time(),
            'production_status' => Constant::BOOLEAN_TRUE
        ], ['id' => $this->route_ids])->execute();

        return true;
    }

}
<?php

namespace app\modules\api\modules\wuliu\forms;

use app\jobs\PackageRouteJob;
use app\modules\api\models\Constant;
use Yii;
use yii\base\Model;
use yii\db\Expression;
use yii\db\Query;
use yii\helpers\ArrayHelper;

/**
 * 批量修改计划包裹路由计划时间
 *
 * @package app\modules\api\modules\wuliu\forms
 */
class BatchChangePackageRoutePlanDatetimeForm extends Model
{

    /**
     * @var int 包裹 id
     */
    public $package_id;

    /**
     * @var array 修改的路由数据
     */
    public $routes;

    public function rules()
    {
        return [
            [['package_id', 'routes'], 'required'],
            ['routes', function ($attribute, $params) {
                $routes = $this->routes;
                if (is_array($routes)) {
                    $hasError = false;
                    foreach ($routes as $route) {
                        if (!isset($route['id']) || !isset($route['days']) || $route['days'] <= 0) {
                            $hasError = true;
                            break;
                        }
                    }
                    if ($hasError) {
                        $this->addError($attribute, '路由计划时间修改数据格式错误。');
                    } else {
                        $n = (new Query())
                            ->from('{{%wuliu_package_route}}')
                            ->where([
                                'package_id' => $this->package_id,
                                'id' => ArrayHelper::getColumn($routes, 'id'),
                            ])
                            ->count();
                        if ($n != count($routes)) {
                            $this->addError($attribute, 'routes 参数错误，请确保提交的路由是否为该包裹所有。');
                        }
                    }
                } else {
                    $this->addError($attribute, '路由计划时间修改数据格式错误。');
                }
            }],
        ];
    }

    public function attributeLabels()
    {
        return [
            'package_route_id' => '包裹路由',
            'days' => '天数',
        ];
    }

    /**
     * 保存
     *
     * @return BatchChangePackageRoutePlanDatetimeForm|bool
     */
    public function save()
    {
        $db = Yii::$app->getDb();
        $transaction = $db->beginTransaction();
        try {
            $cmd = $db->createCommand();
            foreach ($this->routes as $route) {
                $cmd->update('{{%wuliu_package_route}}', [
                    'plan_datetime' => new Expression('[[plan_datetime]] + :n', [':n' => $route['days'] * 86400]),
                    'plan_datetime_is_changed' => Constant::BOOLEAN_TRUE,
                ], ['id' => $route['id']])->execute();
            }
            Yii::$app->queue->push(new PackageRouteJob([
                'id' => $this->package_id,
            ]));

            $transaction->commit();

            return true;
        } catch (\Exception $e) {
            $transaction->rollBack();
            $this->addError('package_id', $e->getMessage());

            return $this;
        } catch (\Throwable $e) {
            $transaction->rollBack();
            $this->addError('package_id', $e->getMessage());

            return $this;
        }
    }

}
<?php

namespace app\modules\api\modules\wuliu\forms;

use app\modules\api\modules\wuliu\models\PackageRoute;
use yii\base\Model;
use yii\web\ServerErrorHttpException;

/**
 * 包裹路由手动处理表单
 *
 * @package app\modules\api\modules\wuliu\forms

 */
class PackageRouteProcessForm extends Model
{

    /**
     * @var $model PackageRoute
     */
    public $model;

    /**
     * @var string 预测时间
     */
    public $plan_datetime;

    /**
     * @var int 处理状态
     */
    public $process_status;

    /**
     * @var string 备注
     */
    public $remark;

    public function rules()
    {
        return [
            [['process_status', 'model'], 'required'],
            ['model', function ($attribute, $params) {
                if ($this->model === null || !$this->model instanceof PackageRoute) {
                    $this->addError($attribute, '载入包裹信息错误。');
                } elseif ($this->model->process_status != PackageRoute::PROCESS_STATUS_PENDING) {
                    $this->addError($attribute, '该节点路由已经处理。');
                }
            }],
            ['process_status', 'integer'],
            ['process_status', 'default', 'value' => PackageRoute::PROCESS_STATUS_COMPLETED],
            ['process_status', 'in', 'range' => [PackageRoute::PROCESS_STATUS_IGNORE, PackageRoute::PROCESS_STATUS_COMPLETED]],
            ['remark', 'trim'],
            ['remark', 'string'],
        ];
    }

    /**
     * 保存
     *
     * @return PackageRouteProcessForm|PackageRoute
     * @throws ServerErrorHttpException
     */
    public function save()
    {
        if ($this->validate()) {
            $this->model->process_status = $this->process_status;
            $this->model->remark = $this->remark;
            if ($this->model->save() === false && !$this->model->hasErrors()) {
                throw new ServerErrorHttpException('处理包裹路由失败，原因未知。');
            }

            return $this->model;
        } else {
            return $this;
        }
    }

    public function attributeLabels()
    {
        return [
            'plan_datetime' => '预测时间',
            'process_status' => '处理状态',
            'remark' => '备注',
        ];
    }

}
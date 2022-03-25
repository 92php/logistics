<?php

namespace app\modules\api\modules\wuliu\forms;

use app\modules\api\modules\wuliu\models\CompanyLine;
use app\modules\api\modules\wuliu\models\CompanyLineRoute;
use app\modules\api\modules\wuliu\models\FreightTemplate;
use app\modules\api\modules\wuliu\models\FreightTemplateFee;
use Yii;
use yii\base\Model;
use function var_dump;

/**
 * 线路、路由、运费表单
 *
 * @package app\modules\api\modules\wuliu\forms

 */
class CompanyLineForm extends Model
{

    /**
     * @var array 线路
     */
    public $line;

    /**
     * @var array 线路路由
     */
    public $lineRoutes;

    /**
     * @var array 运费模板
     */
    public $template;

    /**
     * @var array 运费费用
     */
    public $templateFee;

    /**
     * @var float 运费基数折扣率
     */
    public $freightFeeRate;

    /**
     * @var float 挂号费折扣率
     */
    public $baseFeeRate;

    public function rules()
    {
        return [
            [['line', 'lineRoutes', 'template', 'templateFee'], 'required'],
            [['freightFeeRate', 'baseFeeRate'], 'number'],
            // 线路
            ['line', function ($attribute, $params) {
                $payload = $this->line;
                if (is_array($payload) && $payload) {
                    $model = new CompanyLine();
                    $model->loadDefaultValues();
                    if (!$model->load($payload, '') || !$model->validate()) {
                        $this->addError($attribute, $model->getErrors());
                    }
                } else {
                    $this->addError($attribute, '线路数据格式不正确。');
                }
            }],
            // 线路路由
            ['lineRoutes', function ($attribute, $params) {
                $routes = $this->lineRoutes;
                if ($routes && is_array($routes)) {
                    $hasError = false;
                    $prevStepIndex = null;
                    $errorMessage = '路由格式有误。';
                    foreach ($routes as $i => $route) {
                        if (is_array($route)) {
                            $createNewRecord = true;
                            $lineRoute = null;
                            if (isset($route['id']) && $route['id']) {
                                $lineRoute = CompanyLineRoute::findOne($route['id']);
                                $createNewRecord = $lineRoute === null;
                            }
                            if ($createNewRecord) {
                                $lineRoute = new CompanyLineRoute();
                                $lineRoute->loadDefaultValues();
                            }
                            if (!$lineRoute->load($route, '') || !$lineRoute->validate()) {
                                $hasError = true;
                            } else {
                                if ($lineRoute->step == $prevStepIndex) {
                                    $errorMessage = '请确保路由中的步骤值是唯一的。';
                                    $hasError = true;
                                }
                                $prevStepIndex = $lineRoute->step;
                            }
                        } else {
                            $hasError = true;
                        }
                        if ($hasError) {
                            break;
                        }
                    }
                    if ($hasError) {
                        $this->addError($attribute, $errorMessage);
                    }
                } else {
                    $this->addError($attribute, '路由设置不能为空。');
                }
            }],
            // 快递模板
            ['template', function ($attribute, $params) {
                $payload = $this->template;
                if (is_array($payload) && $payload) {
                    $model = new FreightTemplate();
                    $model->loadDefaultValues();
                    $model->name = (string) time();
                    if (!$model->load($payload, '') || !$model->validate()) {
                        $this->addError($attribute, $model->getErrors());
                    }
                } else {
                    $this->addError($attribute, '物流模板数据格式有误。');
                }
            }],
            // 快递模板费用
            ['templateFee', function ($attribute, $params) {
                foreach ($this->templateFee as $payload) {
                    if (is_array($payload) && $payload) {
                        $model = new FreightTemplateFee();
                        $model->loadDefaultValues();
                        if (!$model->load($payload, '') || !$model->validate()) {
                            $this->addError($attribute, $model->getErrors());
                        }
                    } else {
                        $this->addError($attribute, '物流模板费用数据格式有误。');
                    }
                }
            }],
        ];
    }

    public function attributeLabels()
    {
        return [
            'company_id' => '物流公司',
            'line_name' => '线路名称',
            'line_estimate_days' => '预计天数',
            'routes' => '路由',
            'freight_template_fee_mode' => '计费模式',
            'freight_template_fee_line_id' => '物流线路',
            'freight_template_fee_min_weight' => '最小重量',
            'freight_template_fee_max_weight' => '最大重量',
            'freight_template_fee_first_weight' => '首重',
            'freight_template_fee_first_fee' => '首重费用',
            'freight_template_fee_continued_weight' => '续重',
            'freight_template_fee_continued_fee' => '续重费用',
            'freight_template_fee_base_fee' => '挂号费',
        ];
    }

    /**
     *  保存
     *
     * @return CompanyLineForm|bool
     * @throws \Exception
     * @throws \Throwable
     */
    public function save()
    {
        if ($this->validate()) {
            $db = Yii::$app->db;
            $transaction = $db->beginTransaction();
            try {
                $payload = $this->line;
                if (isset($payload['id'])) {
                    $lineModel = CompanyLine::find()->where(['id' => $payload['id']])->one();
                } else {
                    $lineModel = new CompanyLine();
                    $lineModel->loadDefaultValues();
                }
                $lineModel->load($payload, '');
                $success = $lineModel->save();

                if ($success) {
                    if ($lineModel->getIsNewRecord()) {
                        $existsIdList = [];
                    } else {
                        $existsIdList = $db->createCommand('SELECT [[id]] FROM {{%wuliu_company_line_route}} WHERE [[line_id]] = :lineId', [':lineId' => $lineModel->id])->queryColumn();
                    }
                    foreach ($this->lineRoutes as $i => $route) {
                        if (isset($route['id'])) {
                            $lineRouteModel = CompanyLineRoute::find()->where(['id' => $route['id']])->one();
                            foreach ($existsIdList as $key => $value) {
                                if ($route['id'] == $value) {
                                    unset($existsIdList[$key]);
                                }
                            }
                        } else {
                            $lineRouteModel = new CompanyLineRoute();
                            $lineRouteModel->loadDefaultValues();
                        }

                        $lineRouteModel->load($route, '');
                        $lineRouteModel->line_id = $lineModel->id;
                        $lineRouteModel->step = (int) $i + 1;
                        $success = $lineRouteModel->save();
                        if (!$success) {
                            break;
                        }
                    }
                    if ($success && $existsIdList) {
                        $db->createCommand()->delete('{{%wuliu_company_line_route}}', [
                            'id' => $existsIdList
                        ])->execute();
                    }

                    if ($success) {
                        $payload = $this->template;
                        if (isset($payload['id'])) {
                            $templateModel = FreightTemplate::find()->where(['id' => $payload['id']])->one();
                        } else {
                            $templateModel = new FreightTemplate();
                            $templateModel->loadDefaultValues();
                            $companyName = Yii::$app->getDb()->createCommand("SELECT [[name]] FROM {{%wuliu_company}} WHERE [[id]] = :id", [':id' => $lineModel->company_id])->queryScalar();
                            $templateModel->name = $companyName ?: (string) time();
                        }
                        $templateModel->load($payload, '');
                        $templateModel->save();

                        if ($templateModel->getIsNewRecord()) {
                            $existsIdList = [];
                        } else {
                            $existsIdList = $db->createCommand('SELECT [[id]] FROM {{%wuliu_freight_template_fee}} WHERE [[template_id]] = :templateId', [':templateId' => $templateModel->id])->queryColumn();
                        }

                        foreach ($this->templateFee as $payload) {
                            if (isset($payload['id'])) {
                                $templateFeeModel = FreightTemplateFee::find()->where(['id' => $payload['id']])->one();
                                foreach ($existsIdList as $key => $value) {
                                    if ($payload['id'] == $value) {
                                        unset($existsIdList[$key]);
                                    }
                                }
                            } else {
                                $templateFeeModel = new FreightTemplateFee();
                                $templateFeeModel->loadDefaultValues();
                            }

                            $templateFeeModel->load($payload, '');
                            if ($this->freightFeeRate) {
                                $templateFeeModel->freight_fee_rate = $this->freightFeeRate;
                            }

                            if ($this->baseFeeRate) {
                                $templateFeeModel->base_fee_rate = $this->baseFeeRate;
                            }
                            $templateFeeModel->template_id = $templateModel->id;
                            $templateFeeModel->line_id = $lineModel->id;
                            $success = $templateFeeModel->save();
                            if (!$success) {
                                break;
                            }
                        }
                        if ($success && $existsIdList) {
                            $db->createCommand()->delete('{{%wuliu_freight_template_fee}}', [
                                'id' => $existsIdList
                            ])->execute();
                        }
                    }
                }

                $success ? $transaction->commit() : $transaction->rollBack();

                return true;
            } catch (\Exception $e) {
                $transaction->rollBack();
                throw $e;
            } catch (\Throwable $e) {
                $transaction->rollBack();
                throw $e;
            }
        } else {
            return $this;
        }
    }

}
<?php

namespace app\extensions\logisticsService;

use Exception;
use Symfony\Component\Validator\Constraints\Count;
use Symfony\Component\Validator\Constraints\Date;
use Symfony\Component\Validator\Constraints\DateTime;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Type;
use Symfony\Component\Validator\Validation;
use function Symfony\Component\String\u;

/**
 * 物流服务抽象类
 *
 * @package app\extensions\logisticsService
 */
abstract class LogisticsServiceAbstract
{

    /**
     * @var bool 数据是否有错误
     */
    private $hasError = false;

    /**
     * @var array 错误信息
     */
    private $errors = [];

    /**
     * @var array 配置信息
     */
    public $config;

    /**
     * @var bool 调试模式
     */
    public $debug = true;

    /**
     * @var null|Order 订单数据
     */
    public $order = null;

    /**
     * @var null|Channel 线路
     */
    public $channel = null;

    /**
     * @var null|Country 国家
     */
    public $country = null;

    /**
     * LogisticsAbstract constructor.
     *
     * @throws Exception
     */
    public function __construct()
    {
        $name = strtolower(str_replace('LogisticsService', '', basename(static::class)));
        $path = __DIR__ . "/config/$name.php";
        if (file_exists($path)) {
            $this->config = require_once($path);
        } else {
            throw new Exception("$path config file is not exists.");
        }
    }

    /**
     * XML 数据转换为数组
     *
     * @param $xml
     * @return mixed
     */
    public function xml2array($xml)
    {
        if ($this->debug) {
            $class = ' ' . get_called_class() . ' ';
            $s = u($class)->padBoth(120, '=');
            echo $s . PHP_EOL . PHP_EOL;
            echo $xml . PHP_EOL . PHP_EOL;
            echo $s . PHP_EOL . PHP_EOL;
        }

        $xml = simplexml_load_string($xml);
        $array = json_encode($xml);

        return json_decode($array, true);
    }

    /**
     * 设置需要处理的订单数据
     *
     * @param Order $order
     */
    function setOrder(Order $order)
    {
        $this->order = $order;
    }

    /**
     * 设置路线数据
     *
     * @param Channel $channel
     */
    function setChannel(Channel $channel)
    {
        $this->channel = $channel;
    }

    /**
     * 设置国家
     *
     * @param Country $country
     */
    function setCountry(Country $country)
    {
        $this->country = $country;
    }

    /**
     * 根据指定的路径读取配置文件值
     *
     * @param $path
     * @param null $default
     * @return mixed|null
     */
    public function getConfigValue($path, $default = null)
    {
        $array = $this->config;
        $value = null;
        $keys = is_array($path) ? $path : explode('.', $path);
        while (($n = count($keys)) > 0) {
            $key = array_shift($keys);
            if (isset($array[$key])) {
                $value = $array[$key];
                if ($n == 1) {
                    break;
                }
                $array = $value;
            } else {
                break;
            }
        }

        return $value === null ? $default : $value;
    }

    /**
     * 动态设置配置文件值
     *
     * @param $path
     * @param $value
     */
    public function setConfig($path, $value)
    {
        $array = &$this->config;
        if ($path === null) {
            $array = $value;

            return;
        }

        $keys = is_array($path) ? $path : explode('.', $path);

        while (count($keys) > 1) {
            $key = array_shift($keys);
            if (!isset($array[$key])) {
                $array[$key] = [];
            }
            if (!is_array($array[$key])) {
                $array[$key] = [$array[$key]];
            }
            $array = &$array[$key];
        }

        $array[array_shift($keys)] = $value;
        $this->config = $array;
    }

    public function getName()
    {
        return $this->getConfigValue('name');
    }

    public function getVersion()
    {
        return $this->getConfigValue('version');
    }

    /**
     * 获取接口端点
     *
     * @return string
     */
    public function getEndpoint(): string
    {
        return $this->getConfigValue('endpoint.' . ($this->debug ? 'dev' : 'prod'));
    }

    /**
     * 获取接口地址
     *
     * @param $path
     * @return string
     */
    public function getUrl($path): string
    {
        return $this->getEndpoint() . $path;
    }

    /**
     * 获取物流发货渠道
     *
     * @return array
     */
    abstract public function getChannels(): array;

    /**
     * 获取物流发货国家
     *
     * @return array
     */
    abstract public function getCountries(): array;

    /**
     * 申请运单号
     *
     * @param bool $prediction
     * @param bool $runValidation
     * @return Response
     * @throws Exception
     */
    public function createOrder(bool $prediction = false, bool $runValidation = true): Response
    {
        $order = $this->order;
        if ($order === null || !$order instanceof Order) {
            throw new Exception("未设置订单数据。");
        }

        $runValidation && $this->validate('create-order');
        $resp = new Response();
        $resp->setSuccess(!$this->hasError());
        if ($this->hasError()) {
            foreach ($this->getErrors() as $attribute => $messages) {
                $resp->setErrorMessage($messages[0]);
                break;
            }
        }

        return $resp;
    }

    /**
     * 订单数据验证
     *
     * @param $scene
     * @return bool
     * @throws Exception
     */
    private function validate($scene)
    {
        $order = $this->order;
        if ($order === null || !($order instanceof Order)) {
            throw new Exception('未检测到订单数据，无法验证其数据有效性。');
        }
        $rawValidators = $this->getConfigValue("validators");
        $validators = array_merge($rawValidators['global'] ?? [], $rawValidators[$scene] ?? []);
        if (is_array($validators) && $validators) {
            foreach ($validators as $key => $attr) {
                $getter = 'get' . ucfirst($key);
                $label = $attr['label'] ?? $key;
                $value = $order->$getter();
                $validator = Validation::createValidator();
                $constraints = [];
                foreach ($attr['rules'] as $rule) {
                    $validatorType = $rule[0];
                    switch ($validatorType) {
                        case 'required':
                            $constraints[] = new NotBlank(['message' => "{$label} 不能为空。"]);
                            break;

                        case 'string':
                            $constraints[] = new Length([
                                'min' => $rule['min'] ?? 0,
                                'max' => $rule['max'] ?? 100,
                                'minMessage' => "{$label}数据长度无效，最少 {{ limit }} 个字符。",
                                'maxMessage' => "{$label}数据长度无效，最多 {{ limit }} 个字符。",
                            ]);
                            break;

                        case 'email':
                            $constraints[] = new Email([
                                'message' => "{$label}为无效的电子邮箱地址格式。"
                            ]);
                            break;

                        case 'count':
                            $min = $rule['min'] ?? 1;
                            $min = intval($min);
                            $min <= 0 && $min = 1;
                            $constraints[] = new Count([
                                'min' => $min,
                                'minMessage' => "请至少提供 $min 个订单商品。"
                            ]);
                            break;

                        case 'type':
                            $type = $rule[1] ?? 'string';
                            $type = strtolower($type);
                            switch ($type) {
                                case 'array':
                                    $s = '数组';
                                    break;

                                case 'int':
                                    $s = '数字';
                                    break;

                                case 'float':
                                    $s = '小数';
                                    break;

                                case 'bool':
                                    $s = '布尔值';
                                    break;

                                default:
                                    $s = '字符串';
                                    break;
                            }

                            $constraints[] = new Type([
                                'type' => $type,
                                'message' => "{$label}值必须为{$s}。",
                            ]);
                            break;

                        case 'date':
                            $constraints[] = new Date();
                            break;

                        case 'datetime':
                            $constraints[] = new DateTime();
                            break;
                    }
                }
                if ($constraints) {
                    $violations = $validator->validate($value, $constraints);
                    if (0 !== count($violations)) {
                        // there are errors, now you can show them
                        foreach ($violations as $violation) {
                            $this->setError($key, $violation->getMessage());
                        }

                        return false;
                    }
                }
            }
        }

        return true;
    }

    /**
     * @return bool
     */
    public function hasError(): bool
    {
        return count($this->getErrors()) != 0;
    }

    /**
     * @return array
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * @param $attribute
     * @param $message
     */
    public function setError($attribute, $message): void
    {
        $this->errors[$attribute] = [];
        $this->errors[$attribute][] = $message;
    }

}

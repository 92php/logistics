<?php

namespace app\jobs;

use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;
use Yii;
use yii\queue\RetryableJobInterface;

/**
 * Class OmDxmOrderStatusJob
 *
 * om模块订单与店小秘同步，供应商接单后的订单移入待打单，完成的订单移入申请运单号
 *
 * @package app\jobs
 */
class OmDxmOrderStatusJob extends Job implements RetryableJobInterface
{

    /**
     * 类型
     */
    const TYPE_APPLY_NUMBER = 1; // 申请运单号
    const TYPE_PENDING_PRINT = 2; // 待打单

    /**
     * @var int 包裹id
     */
    public $id;

    /**
     * @var int 类型，根据不同类型区分申请运单号和待打单 （1：申请运单号， 2：待打单）
     */
    public $type;

    /**
     * @inheritDoc
     * @throws \yii\db\Exception
     * @throws \Exception
     */
    public function execute($queue)
    {
        $package = Yii::$app->getDb()->createCommand("SELECT [[p.key]], [[p.number]], [[t.authentication_config]] FROM {{%g_package}} p INNER JOIN {{%g_shop}} s ON [[p.shop_id]] = [[s.id]] INNER JOIN {{%g_third_party_authentication}} t ON [[t.id]] = [[s.third_party_authentication_id]] WHERE [[p.id]] = :id", [':id' => $this->id])->queryOne();
        if ($package === false) {
            throw new Exception("{$this->id} 订单不存在。");
        }
        $success = false;
        $errorMessage = '';
        $authenticationConfig = json_decode($package['authentication_config'], true);
        if (isset($authenticationConfig['dianxiaomi']['cookie'])) {
            $cookies = [];
            foreach (explode(';', $authenticationConfig['dianxiaomi']['cookie']) as $item) {
                try {
                    list($key, $value) = explode('=', $item);
                    $key = trim($key);
                    $value = trim($value);
                    $cookies[$key] = $value;
                } catch (\Exception $e) {
                    $errorMessage = 'Invalid cookies in dxm account [' . $authenticationConfig['username'] . '].';
                    break;
                }
            }
            if ($cookies) {
                $client = new Client([
                    'headers' => [
                        'user-agent' => 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/74.0.3729.157 Safari/537.36',
                        'host' => 'www.xxx.com',
                        'Referer' => 'https://www.xxx.com/order/index.htm',
                    ],
                    'cookies' => CookieJar::fromArray($cookies, 'www.xxx.com'),
                ]);
                if ($this->type == self::TYPE_APPLY_NUMBER) {
                    // 申请运单号
                    $url = "https://www.xxx.com/package/moveProcessed.json";
                } else {
                    // 待打单
                    $url = "https://www.xxx.com/package/moveAllocated.json";
                }
                $response = $client->post($url, [
                    'form_params' => [
                        'packageId' => $package['key'],
                    ]
                ]);
                $msg = "Package #{$this->id} Number: {$package['number']} 状态修改为：";
                switch ($this->type) {
                    case self::TYPE_APPLY_NUMBER:
                        $msg .= '申请运单号';
                        break;

                    case self::TYPE_PENDING_PRINT:
                        $msg .= '待打单';
                        break;

                    default:
                        $msg .= '未知';
                        break;
                }
                $msg .= ' 处理结果：';
                if ($response->getStatusCode() == '200') {
                    $json = json_decode($response->getBody()->getContents(), true);
                    if ($json) {
                        if (isset($json['code']) && $json['code'] == -1) {
                            $errorMessage = "{$msg} 失败，原因：{$json['msg']}";
                        } else {
                            $success = true;
                            $this->info("{$msg} 成功");
                        }
                    } else {
                        $errorMessage = "$msg 失败，原因：{$response->getBody()->getContents()}";
                    }
                } else {
                    $errorMessage = "HTTP error code: {$response->getStatusCode()}, response: {$response->getBody()->getContents()}";
                }
            } else {
                $errorMessage = "This cookie is valid.";
            }
        } else {
            $errorMessage = 'Invalid cookies in dxm account [' . $authenticationConfig['username'] . '].';
        }
        if (!$success) {
            $this->error($errorMessage);
            throw new \Exception($errorMessage);
        }
    }

    /**
     * @inheritDoc
     */
    public function getTtr()
    {
        return 10 * 60;
    }

    /**
     * @inheritDoc
     */
    public function canRetry($attempt, $error)
    {
        return ($attempt < 6) && ($error instanceof \Exception);
    }

}
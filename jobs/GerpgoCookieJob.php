<?php

namespace app\jobs;

use Exception;
use GuzzleHttp\Client;
use Yii;
use yii\helpers\ArrayHelper;
use yii\queue\JobInterface;

/**
 * cookie 获取任务
 *
 * @package app\jobs
 */
class GerpgoCookieJob extends Job implements JobInterface
{

    /**
     * @var int 第三方认证编号
     */
    public $id;

    /**
     * @param \yii\queue\Queue $queue
     * @return mixed|void
     * @throws \yii\db\Exception
     * @throws Exception
     */
    public function execute($queue)
    {
        $db = Yii::$app->db;
        $config = $db->createCommand("SELECT [[authentication_config]] FROM {{%g_third_party_authentication}} WHERE [[id]] = :id", [':id' => $this->id])->queryScalar();
        if ($config) {
            $config = json_decode($config, true);
            if ($config) {
                $username = ArrayHelper::getValue($config, 'gerpgo.username');
                $password = ArrayHelper::getValue($config, 'gerpgo.password');
                if ($username && $password) {
                    try {
                        $client = new Client([
                            'headers' => [
                                'user-agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/83.0.4103.97 Safari/537.36',
                                'host' => 'xxxx.com',
                                'Origin' => 'http://kkxq.xxx.com',
                                'Referer' => 'http://kkxq.xxx.com/sale/order',

                            ]
                        ]);
                        $response = $client->post('http://xxx.com/v2/system/account/login', [
                            'form_params' => [
                                'username' => $username,
                                'password' => $password,
                            ]
                        ]);
                        $cookies = $response->getHeader('Set-Cookie');
                        $config['gerpgo']['cookie'] = implode(';', $cookies);
                        $db->createCommand()->update('{{%g_third_party_authentication}}', [
                            'authentication_config' => $config,
                        ], ['id' => $this->id])->execute();
                    } catch (\Exception $e) {
                        $this->error($e->getMessage());
                    }
                } else {
                    $this->error("Not found username or password.");
                }
            }
        } else {
            $this->error("Not found the third party authentication with id [" . $this->id . "]" . PHP_EOL);
        }
    }

}
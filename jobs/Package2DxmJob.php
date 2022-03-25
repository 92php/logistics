<?php

namespace app\jobs;

use app\modules\api\modules\wuliu\models\Package;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;
use Yii;
use yii\queue\JobInterface;

/**
 * 同步包裹数据到店小秘
 *
 * @package app\jobs
 */
class Package2DxmJob extends Job implements JobInterface
{

    /**
     * @var int 包裹 id
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
        $db = Yii::$app->getDb();
        $id = explode(',', $this->id);
        $packages = $db->createCommand('SELECT [[p.id]],[[p.key]], [[p.number]], [[p.weight]], [[t.authentication_config]] FROM {{%g_package}} p INNER JOIN {{%g_package_order_item}} poi ON [[poi.package_id]] = [[p.id]] INNER JOIN {{%g_order}} o ON o.id = poi.order_id INNER JOIN {{%g_shop}} s ON o.shop_id = s.id INNER JOIN {{%g_third_party_authentication}} t ON t.id = s.third_party_authentication_id WHERE [[p.id]] IN (' . implode(',', $id) . ') AND [[p.sync_status]] = :syncStatus', [
            ':syncStatus' => Package::SYNC_PENDING
        ])->queryAll();
        foreach ($packages as $package) {
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
                        $this->error('Invalid cookies in dxm account [' . $package['dianxiaomi']['cookie'] . '].' . PHP_EOL);
                        break;
                    }
                }
                $client = new Client([
                    'headers' => [
                        'user-agent' => 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/74.0.3729.157 Safari/537.36',
                        'host' => 'www.xxx.com',
                        'Referer' => 'https://www.xxx.com/order/index.htm',
                    ],
                    'cookies' => CookieJar::fromArray($cookies, 'www.xxx.com'),
                ]);
                $response = $client->post('https://www.xxx.com/package/getPackageByCode.htm', [
                    'form_params' => [
                        'code' => $package['number'],
                        'weight' => $package['weight'],
                        'weightUnit' => 'g',
                    ]
                ]);
                if ($response->getStatusCode() == '200') {
                    $response = $client->post('https://www.xxx.com/package/shipGoods.json', [
                        'form_params' => [
                            'packageId' => $package['key'],
                        ]
                    ]);
                    if ($response->getStatusCode() == '200') {
                        $response = json_decode($response->getBody()->getContents(), true);
                        if ($response) {
                            if (isset($response['code']) && $response['code'] == -1) {
                                $this->error('Package [' . $this->id . '] failed with message:' . $response['msg'] . PHP_EOL);
                            } else {
                                $db->createCommand()->update('{{%g_package}}', ['sync_status' => Package::SYNC_SUCCESSFUL], ['id' => $package['id']])->execute();
                                $this->info('Package [' . $this->id . '] success' . PHP_EOL);
                            }
                        } else {
                            $this->error('Package [' . $this->id . '] failed with invalid dxm account [' . $package['dxm_account_id'] . ']' . PHP_EOL);
                        }
                    }
                } else {
                    $this->error('Error with HTTP CODE : ' . $response->getStatusCode() . PHP_EOL);
                }
            } else {
                $this->error('Package [' . $package['id'] . '] failed with invalid dxm account [' . $package['dxm_account_id'] . ']' . PHP_EOL);
            }
        }
        $this->info('Done package [' . $this->id . ']' . PHP_EOL);
    }

}
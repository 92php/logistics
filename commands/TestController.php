<?php

namespace app\commands;

use app\extensions\logisticsService\Channel;
use app\extensions\logisticsService\Country;
use app\extensions\logisticsService\Order;
use app\extensions\logisticsService\OrderItem;
use app\extensions\logisticsService\YanWenLogistics;
use app\extensions\logisticsService\YanWenLogisticsService;
use app\jobs\OmDxmOrderStatusJob;
use class_with_method_that_declares_anonymous_class;
use GuzzleHttp\Client;
use Yii;
use function Symfony\Component\String\u;
use const PHP_EOL;

class TestController extends Controller
{

    public function actionA()
    {
        $o = \app\modules\admin\modules\g\models\Order::find()->where(['number' => '234'])->one();
        var_dump($o);
       exit;

//        $id = 0;
//        $command = Yii::$app->db->createCommand('SELECT name FROM {{%wuliu_company}} WHERE id=:id')
//            ->bindParam(':id', $id);
//
//        $id = 1;
//        $post1 = $command->queryScalar();
//        var_dump($post1);
//
//        $id = 2;
//        $post2 = $command->queryScalar();
//        var_dump($post2);
    }

    public function actionTest()
    {
        $db = Yii::$app->getDb();
        $skuVendors = $db->createCommand("SELECT * FROM {{%om_sku_vendor}}")->queryAll();
        $items = [];
        foreach ($skuVendors as $skuVendor) {
            $items[] = [
                'sku_vendor_id' => $skuVendor['id'],
                'cost_price' => $skuVendor['cost_price'],
                'quantity' => 1,
            ];
        }
        $db->createCommand()->batchInsert("{{%om_sku_vendor_many_cost}}", array_keys($items[0]), $items)->execute();
    }

    public function actionIndex()
    {
        $s = "I❤😄U";
        $s = u($s)->trim()->toString();
        \Yii::$app->getDb()->createCommand()->update('{{%g_order}}', ['remark' => $s], ['id' => 1])->execute();

        echo $s;
        exit;
        $logistics = new YanWenLogisticsService();
        $config = $logistics->config;
//        $logistics->setConfig('name', 'b1');
//        var_dump($logistics->getConfigValue('serviceEndpoint.prod'));
//        exit;

        $channel = new Channel();
        $channels = $logistics->getChannels();
        foreach ($channels as $item) {
            /* @var $item Channel */
            $channel = $item;
            break;
//            $this->stdout($channel->getId() . ':' . $channel->getChineseName() . PHP_EOL);
        }

        $country = $logistics->getCountries($channel);
        foreach ($countries as $item) {
            /* @var $item Country */
            $country = $item;
            break;
        }

        try {
            $orderItem = new OrderItem();
            $orderItem->setChineseName("中文产品名");
            $orderItem->setEnglishName("English product name");
            $orderItem->setPrice(1.23);
            $orderItem->setQuantity(10);
            $order = new Order();
            $order->setNumber("12345");
            $order->setSenderCountry('China');
            $order->setReceiverName("John");
            $order->setReceiverMobilePhone("1-3333");
            $order->setReceiverCountry(99);
            $order->setReceiverState("Hu Nan");
            $order->setReceiverCity("Chang Sha");
            $order->setReceiverAddress1("Lu Gu niubi xingqiu");
            $order->setReceiverPostcode("410000");
            $order->setRemark("This is a test order");
            $order->setChannel(368);
            $order->setItem($orderItem);
            $logistics->createOrder($order);
        } catch (\Exception $e) {
            echo $e->getMessage();
        }
        exit;
        $this->stdout($logistics->getName() . ' ' . $logistics->getVersion() . PHP_EOL);
        exit;
        $channels = $logistics->getChannels();
        foreach ($channels as $channel) {
            /* @var $channel Channel */
            $this->stdout($channel->getId() . ':' . $channel->getChineseName() . PHP_EOL);
        }

        exit;
        $s = '1634955-4360 1634955-4320 ';
        $s = u($s)->collapseWhitespace()->toString();
        print_r(explode(' ', $s));
        exit;
        $url = "https://www.baidu.com";
        $urls = "https://httpbin.org/ip";

        define("PROXY_SERVER", "tcp://t.16yun.cn:31111");

        define("PROXY_USER", "16YUN123");
        define("PROXY_PASS", "123456");

        $proxyAuth = base64_encode(PROXY_USER . ":" . PROXY_PASS);

        $tunnel = rand(1, 10000);

        $headers = implode("\r\n", [
            "Proxy-Authorization: Basic {$proxyAuth}",
            "Proxy-Tunnel: ${tunnel}",
        ]);
        $sniServer = parse_url($urls, PHP_URL_HOST);
        $options = [
            "http" => [
                "proxy" => PROXY_SERVER,
                "header" => $headers,
                "method" => "GET",
                'request_fulluri' => true,
            ],
            'ssl' => array(
                'SNI_enabled' => true, // Disable SNI for https over http proxies
                'SNI_server_name' => $sniServer
            )
        ];
        print($url);
        $context = stream_context_create($options);

        $result = file_get_contents($url, false, $context);
        var_dump($result);
        print($urls);
        $context = stream_context_create($options);
        $result = file_get_contents($urls, false, $context);
        var_dump($result);
    }

    public function actionJob()
    {
        Yii::$app->queue->push(new OmDxmOrderStatusJob([
            'id' => 31,
            'type' => 1,
        ]));
    }

    public function actionCookie()
    {
        $client = new Client([
            'headers' => [
                'user-agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/83.0.4103.97 Safari/537.36',
                'host' => '127.0.0.1',
                'Origin' => 'http://kkxq.xxx.com',
                'Referer' => 'http://kkxq.xxx.com/sale/order',

            ]
        ]);
        try {
            $response = $client->post('http://127.0.0.1/v2/system/account/login', [
                'form_params' => [
                    'username' => '',
                    'password' => '',
                ]
            ]);
            $cookies = $response->getHeader('Set-Cookie');
            $cookie = $cookies[4] ?? null;
            echo $cookie;
        } catch (\Exception $e) {
            $this->stderr($e->getMessage());
        }
    }

}

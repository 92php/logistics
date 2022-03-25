<?php

namespace app\commands\classes;

use app\models\Constant;
use DateTime;
use Exception;
use GuzzleHttp\Client;
use InvalidArgumentException;
use yii\helpers\Console;

/**
 * 数据处理
 *
 * @package app\command\classes
 */
class TongToolDataProvider extends ThirdPartyDataProvider
{

    /**
     * @var string $app_token 访问令牌
     */
    private $app_token = '';

    /**
     * @var string $merchantId 用户ID
     */
    private $merchantId = '';

    /**
     * @var string Account Code
     */
    private $accountCode;

    /**
     * @var string Account
     */
    private $account;

    /**
     * 获取订单数据
     * 店铺的 sign 设置为 accountCode|account,例如：QPLIFEEU|liangzi@163.com
     *
     * @param array $identity identity => ['app_key' => 'xxx', 'app_secret' => 'xxx']
     * @param Shop|null $shop
     * @param DateTime $datetime
     * @return array
     * @throws Exception
     */
    public function getOrders(array $identity, Shop $shop = null, DateTime $datetime = null): array
    {
        $identity = $identity['tongtool'];
        $this->getToken($identity);
        $now = time();
        $sign = md5('app_token' . $this->app_token . 'timestamp' . $now . $identity['app_secret']);
        $auth = 'app_token=' . $this->app_token . '&timestamp=' . $now . '&sign=' . $sign;
        $shopSign = $shop->getSign();
        if ($shopSign) {
            if (strpos($shopSign, '|') === false) {
                $this->accountCode = $shopSign;
            } else {
                list($this->accountCode, $this->account) = explode('|', $shopSign);
            }
        }
        $maxSeconds = 12;
        $now = time();
        $amazonOrders = $this->getAmazonOrders($auth, $shop, $datetime);
        $seconds = time() - $now;
        if ($seconds < $maxSeconds) {
            $seconds = $maxSeconds - $seconds;
            Console::stdout("Please waiting {$seconds} seconds..." . PHP_EOL);
            sleep($seconds);
        }
        $fbaOrders = $this->getFbaOrders($auth, $shop, $datetime);

        return array_merge($fbaOrders, $amazonOrders);
    }

    /**
     * @param string $auth
     * @param Shop $shop
     * @param DateTime $datetime
     * @return array
     * @throws \yii\base\Exception
     * @throws Exception
     */
    protected function getAmazonOrders($auth, Shop $shop, DateTime $datetime)
    {
        $orders = [];
        $client = $this->getClient();
        // 付款时间筛选
        $payDateFrom = $datetime->setTime(0, 0, 0)->format('Y-m-d H:i:s');
        $payDateTo = $datetime->setTime(23, 59, 59)->format('Y-m-d H:i:s');
        // 翻页处理
        $pageNo = 1;
        $pageSize = 100;
        while (true) {
            Console::stdout("> Page # $pageNo" . PHP_EOL);
            $response = $client->post('https://open.xxx.com/api-service/openapi/tongtool/ordersQuery?' . $auth, [
                'json' => [
                    'accountCode' => $this->accountCode,
                    'pageSize' => $pageSize,
                    'pageNo' => $pageNo,
                    'payDateFrom' => $payDateFrom,
                    'payDateTo' => $payDateTo,
                    'merchantId' => $this->merchantId,
                ]
            ]);
            $res = json_decode($response->getBody()->getContents(), true);
            if ($res['code'] != 200) {
                throw new Exception("Request Failed With Error : " . $res['message'] . PHP_EOL);
            } else {
                $rawOrders = $res['datas']['array'] ?? [];
                foreach ($rawOrders as $order) {
                    $orderModel = new Order();
                    $orderModel->setNumber(isset($order['salesRecordNumber']) ? $order['salesRecordNumber'] : '');
                    $orderModel->setConsigneeName(isset($order['buyerName']) ? $order['buyerName'] : '');
                    $orderModel->setConsigneeMobilePhone(isset($order['buyerMobile']) ? $order['buyerMobile'] : '');
                    $orderModel->setConsigneeTel(isset($order['buyerPhone']) ? $order['buyerPhone'] : '');
                    $orderModel->setCountry(isset($order['buyerCountry']) ? $order['buyerCountry'] : '');
                    $orderModel->setConsigneeState(isset($order['buyerState']) ? $order['buyerState'] : '');
                    $orderModel->setConsigneeCity(isset($order['buyerCity']) ? $order['buyerCity'] : '');
                    $orderModel->setConsigneeAddress1(isset($order['receiveAddress']) ? $order['receiveAddress'] : '');
                    $orderModel->setConsigneePostcode(isset($order['postalCode']) ? $order['postalCode'] : '');
                    $orderModel->setTotalAmount(isset($order['orderAmount']) ? $order['orderAmount'] : '');
                    $orderModel->setPlatformId(Constant::PLATFORM_AMAZON);
                    $orderModel->setShopId($shop->getId());
                    $orderModel->setPlaceOrderAt(isset($order['saleTime']) ? strtotime($order['saleTime']) : '');
                    $orderModel->setPaymentAt(isset($order['paidTime']) ? strtotime($order['paidTime']) : '');
                    $orderModel->setWaybillNumber(isset($order['packageInfoList'][0]['trackingNumber']) ? $order['packageInfoList'][0]['trackingNumber'] : '');
                    foreach ($order['orderDetails'] as $item) {
                        $orderItemModel = new OrderItem();
                        $orderItemModel->setProductName($item['webstore_sku']);
                        $orderItemModel->setQuantity($item['quantity']);
                        $orderItemModel->setSku($item['webstore_sku']);
                        $orderItemModel->setSalePrice($item['transaction_price']);
                        $orderItemModel->setImage('');
                        if ($order['goodsInfo']['tongToolGoodsInfoList']) {
                            $tongtoolSku = $item['goodsMatchedSku'];
                            foreach ($order['goodsInfo']['tongToolGoodsInfoList'] as $info) {
                                if ($info['goodsSku'] == $tongtoolSku) {
                                    $orderItemModel->setProductName($info['productName']);
                                    if ($img = $info['goodsImageGroupId']) {
                                        if (strncasecmp($img, 'http', 4) !== 0) {
                                            $img = "https://erp112.xxx.com/file" . $img;
                                        }
                                        $imagePath = $this->downloadImage($img, $orderItemModel->getSku());
                                        if ($imagePath) {
                                            $orderItemModel->setImage($imagePath);
                                        }
                                    }
                                }
                            }
                        }

                        $orderModel->setItem($orderItemModel);
                    }
                    $orders[] = $orderModel;
                }
                if (!isset($rawOrders[$pageSize - 1])) {
                    break; // 如果订单数小于 pageSize, 停止翻页
                }
            }
            $pageNo += 1;
        }

        return $orders;
    }

    /**
     * @param $auth
     * @param Shop|null $shop
     * @param DateTime|null $datetime
     * @return array
     * @throws Exception
     */
    protected function getFbaOrders($auth, Shop $shop = null, DateTime $datetime = null)
    {
        $orders = [];
        $client = $this->getClient();
        // 付款时间筛选
        $payDateFrom = $datetime->setTime(0, 0, 0)->format('Y-m-d H:i:s');
        $payDateTo = $datetime->setTime(23, 59, 59)->format('Y-m-d H:i:s');
        // 翻页处理
        $pageNo = 1;
        $pageSize = 100;
        while (true) {
            $response = $client->post('https://open.xxx.com/api-service/openapi/tongtool/fbaOrderQuery?' . $auth, [
                'json' => [
                    'account' => $this->account,
                    'pageSize' => $pageSize,
                    'pageNo' => $pageNo,
                    'purchaseDateFrom' => $payDateFrom,
                    'purchaseDateTo' => $payDateTo,
                    'merchantId' => $this->merchantId,
                ]
            ]);
            $res = json_decode($response->getBody()->getContents(), true);
            if ($res['code'] != 200) {
                throw new Exception("Request Failed With Error : " . $res['message'] . PHP_EOL);
            } else {
                $rawOrders = $res['datas']['array'] ?? [];
                foreach ($rawOrders as $order) {
                    // FBA 订单没有 Item
                    $orderModel = new Order();
                    $orderModel->setNumber(isset($order['orderId']) ? $order['orderId'] : '');
                    $orderModel->setConsigneeName(isset($order['buyerName']) ? $order['buyerName'] : '');
                    $orderModel->setConsigneeMobilePhone(isset($order['buyerMobile']) ? $order['buyerMobile'] : '');
                    $orderModel->setConsigneeTel(isset($order['buyerPhoneNumber']) ? $order['buyerPhoneNumber'] : '');
                    $orderModel->setCountry(isset($order['shipCountry']) ? $order['shipCountry'] : '');
                    $orderModel->setConsigneeState(isset($order['buyerState']) ? $order['buyerState'] : '');
                    $orderModel->setConsigneeCity(isset($order['buyerCity']) ? $order['buyerCity'] : '');
                    $orderModel->setConsigneeAddress1(isset($order['shipAddress1']) ? $order['shipAddress1'] : '');
                    $orderModel->setConsigneePostcode(isset($order['shipPostalCode']) ? $order['shipPostalCode'] : '');
                    $orderModel->setTotalAmount(isset($order['totalItemPrice']) ? $order['totalItemPrice'] : '');
                    $orderModel->setPlatformId(Constant::PLATFORM_AMAZON);
                    $orderModel->setShopId($shop->getId());
                    $orderModel->setPlaceOrderAt(isset($order['purchaseDate']) ? $order['purchaseDate'] : '');
                    $orderModel->setPaymentAt(isset($order['paymentsDate']) ? $order['paymentsDate'] : '');

                    $orders[] = $orderModel;
                }
                if (!isset($rawOrders[$pageSize - 1])) {
                    break; // 如果订单数小于 pageSize, 停止翻页
                }
            }
            $pageNo += 1;
        }

        return $orders;
    }

    /**
     * Get tongtool client
     *
     * @return Client
     */
    protected function getClient()
    {
        $config = [
            'headers' => [
                'user-agent' => 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/74.0.3729.157 Safari/537.36',
                'content-type' => 'application/json',
                'api_version' => '3.0',
            ],
        ];

        return new Client($config);
    }

    /**
     * Get app_token and merchantId
     *
     * @param $identity
     */
    protected function getToken($identity)
    {
        if (isset($identity['app_key']) && isset($identity['app_secret'])) {
            $client = $this->getClient();
            $url = 'https://open.xxx.com/open-platform-service/devApp/appToken';
            $response = $client->get($url . '?accessKey=' . $identity['app_key'] . '&secretAccessKey=' . $identity['app_secret']);
            $json = $response->getBody()->getContents();
            $res = json_decode($json, true);
            if ($res['success'] && isset($res['datas'])) {
                $this->app_token = $res['datas'];
                $now = time();
                $response = $client->get('https://open.xxx.com/open-platform-service/partnerOpenInfo/getAppBuyerList?app_token=' . $this->app_token . '&timestamp=' . $now . '&sign=' . md5('app_token' . $this->app_token . 'timestamp' . $now . $identity['app_secret']));
                $json = $response->getBody()->getContents();
                $res = json_decode($json, true);
                if ($res['success'] && isset($res['datas'])) {
                    $this->merchantId = $res['datas'][0]['partnerOpenId'];
                } else {
                    throw new InvalidArgumentException("Invalid identity with response: " . $json);
                }
            } else {
                throw new InvalidArgumentException("Invalid identity with response: " . $json);
            }
        } else {
            throw new InvalidArgumentException("The param app_key and app_secret is required!");
        }
    }

    /**
     * 停留时间处理
     *
     * 通途 API 有每分钟五次的限制，超过限制会返回空数据。
     *
     * @param $prevTime
     * @param null $message
     */
    private function sleep($prevTime, $message = null)
    {
        $seconds = 12;
        $now = time();
        $diffSeconds = $now - $prevTime;
        if ($diffSeconds < $seconds) {
            $diffSeconds = $seconds - $diffSeconds;
            Console::stdout($message . " Sleep {$diffSeconds} seconds: ");
            for ($i = 1; $i <= $diffSeconds; $i++) {
                Console::stdout(($diffSeconds - $i + 1) . ' ');
                sleep(1);
            }
            Console::stdout(PHP_EOL);
        }
    }

    /**
     * 自发货包裹
     *
     * @param string $auth
     * @param Shop $shop
     * @param DateTime $beginDate
     * @param DateTime|null $endDate
     * @return array
     * @throws \yii\base\Exception
     */
    private function getSelfPackages($auth, Shop $shop, DateTime $beginDate, Datetime $endDate = null)
    {
        $packages = [];
        $client = $this->getClient();
        // 付款时间筛选
        $payDateFrom = $beginDate->setTime(0, 0, 0)->format('Y-m-d H:i:s');
        $beginDate->modify("+11 days");
        $payDateTo = $beginDate->setTime(23, 59, 59)->format('Y-m-d H:i:s');
        // 翻页处理
        $pageNo = 1;
        $pageSize = 100;
        while (true) {
            $timestamp = time();
            Console::stdout("> Self Page # $pageNo" . PHP_EOL);
            $response = $client->post('https://open.xxx.com/api-service/openapi/tongtool/ordersQuery?' . $auth, [
                'json' => [
                    'accountCode' => $this->accountCode,
                    'pageSize' => $pageSize,
                    'pageNo' => $pageNo,
                    'payDateFrom' => $payDateFrom,
                    'payDateTo' => $payDateTo,
                    'merchantId' => $this->merchantId,
                ]
            ]);
            $res = json_decode($response->getBody()->getContents(), true);
            if ($res['code'] != 200) {
                throw new Exception("Request Failed With Error : " . $res['message'] . PHP_EOL);
            } else {
                $rawOrders = $res['datas']['array'] ?? [];
                $orderIds = [];
                foreach ($rawOrders as $order) {
                    $orderId = $order['orderIdCode'] ?? null;
                    $orderIds[] = $orderId;
                    $packageClass = new Package();
                    $packageClass->setShopId($shop->getId());
                    $packageClass->setWaybillNumber($order['packageInfoList'][0]['trackingNumber'] ?? null);
                    // 获取包裹信息
//                    if ($packageClass->getWaybillNumber()) {
//                        $this->sleep($timestamp, "packagesQuery: $orderId");
//                        $timestamp = time();
//                        $packageQueryResponse = $client->post('https://open.xxx.com/api-service/openapi/tongtool/packagesQuery?' . $auth, [
//                            'json' => [
//                                'merchantId' => $this->merchantId,
//                                'orderId' => $orderId,
//                            ]
//                        ]);
//                        if ($packageQueryResponse->getStatusCode() == 200) {
//                            $res = json_decode($packageQueryResponse->getBody()->getContents(), true);
//                            if ($res['code'] = 200 && isset($res['datas']['array'])) {
//                                $responseData = $res['datas']['array'][0] ?? [];
//                                if ($responseData) {
//                                    $packageClass->setKey($responseData['packageId'] ?? null);
//                                    $packageClass->setNumber($responseData['packageId'] ?? null);
//                                    $packageClass->setLogisticsLineName($responseData['shippingMethodName'] ?? null);
//                                }
//                            }
//                        }
//                    }

                    $orderClass = new Order();
                    $orderClass->setShopId($shop->getId());
                    $packageClass->setOrders($orderClass);
                    $orderClass->setNumber($orderId);
                    $orderClass->setConsigneeName($order['buyerName'] ?? null);
                    $orderClass->setConsigneeMobilePhone($order['buyerMobile'] ?? null);
                    $orderClass->setConsigneeTel($order['buyerPhone'] ?? null);
                    $orderClass->setCountry($order['buyerCountry'] ?? null);
                    $orderClass->setConsigneeState($order['buyerState'] ?? null);
                    $orderClass->setConsigneeCity($order['buyerCity'] ?? null);
                    $orderClass->setConsigneeAddress1($order['receiveAddress'] ?? null);
                    $orderClass->setConsigneePostcode($order['postalCode'] ?? null);
                    $orderClass->setTotalAmount($order['orderAmount'] ?? 0);
                    $orderClass->setPlatformId($shop->getPlatformId());
                    $orderClass->setPlaceOrderAt(isset($order['saleTime']) ? strtotime($order['saleTime']) : '');
                    $orderClass->setPaymentAt(isset($order['paidTime']) ? strtotime($order['paidTime']) : '');
                    foreach ($order['orderDetails'] as $item) {
                        $orderItemClass = new OrderItem();
                        $orderItemClass->setProductName($item['webstore_sku']);
                        $orderItemClass->setQuantity($item['quantity']);
                        $orderItemClass->setSku($item['goodsMatchedSku']);
                        $orderItemClass->setSalePrice($item['transaction_price']);
                        $orderItemClass->setImage('');
                        if ($order['goodsInfo']['tongToolGoodsInfoList']) {
                            foreach ($order['goodsInfo']['tongToolGoodsInfoList'] as $info) {
                                if ($info['goodsSku'] == $orderItemClass->getSku()) {
                                    $orderItemClass->setProductName($info['productName']);
                                    if ($img = $info['goodsImageGroupId']) {
                                        if (strncasecmp($img, 'http', 4) !== 0) {
                                            $img = "https://erp112.xxx.com/file" . $img;
                                        }
                                        $imagePath = $this->downloadImage($img, $orderItemClass->getSku());
                                        if ($imagePath) {
                                            $orderItemClass->setImage($imagePath);
                                        }
                                    }
                                    break;
                                }
                            }
                        }

                        $orderClass->setItem($orderItemClass);
                    }
                    $packages[] = $packageClass;
                }
                if (!isset($rawOrders[$pageSize - 1])) {
                    break; // 如果订单数小于 pageSize, 停止翻页
                }
            }
            $pageNo += 1;
            $timestamp = time();
            $this->sleep($timestamp, "ordersQuery Page $pageNo");
        }

        return $packages;
    }

    /**
     * FBA 包裹
     *
     * @param string $auth
     * @param Shop $shop
     * @param DateTime $beginDate
     * @param DateTime|null $endDate
     * @return array
     * @throws \yii\base\Exception
     * @todo 获取订单详情
     */
    private function getFbaPackages($auth, Shop $shop, DateTime $beginDate, Datetime $endDate = null)
    {
        $packages = [];

        return $packages; // 暂未实现，目前通过接口不到订单的详情数目
        $client = $this->getClient();
        // 付款时间筛选
        $payDateFrom = $beginDate->setTime(0, 0, 0)->format('Y-m-d H:i:s');
        $beginDate->modify("+11 days");
        $payDateTo = $beginDate->setTime(23, 59, 59)->format('Y-m-d H:i:s');
        // 翻页处理
        $pageNo = 1;
        $pageSize = 100;
        while (true) {
            Console::stdout("> FBA Page # $pageNo" . PHP_EOL);
            $response = $client->post('https://open.xxx.com/api-service/openapi/tongtool/fbaOrderQuery?' . $auth, [
                'json' => [
                    'account' => $this->account,
                    'pageSize' => $pageSize,
                    'pageNo' => $pageNo,
                    'purchaseDateFrom' => $payDateFrom,
                    'purchaseDateTo' => $payDateTo,
                    'merchantId' => $this->merchantId,
                ]
            ]);
            $res = json_decode($response->getBody()->getContents(), true);
            if ($res['code'] != 200) {
                throw new Exception("Request Failed With Error : " . $res['message'] . PHP_EOL);
            } else {
                $rawOrders = $res['datas']['array'] ?? [];
                foreach ($rawOrders as $order) {
                    print_r($order);
                    exit;
                    $orderNumber = $order['orderId'];
                    $packageResposne = $client->post('https://open.xxx.com/api-service/openapi/tongtool/packagesQuery?' . $auth, [
                        'json' => [
                            'merchantId' => $this->merchantId,
                            'orderId' => 'QPLIFEUS-114-1384210-0349829',
                        ]
                    ]);
                    print_r($packageResposne->getBody()->getContents());
                    exit;

                    $packageClass = new Package();
                    $packageClass->setNumber($order['packageInfoList'][0]['trackingNumber'] ?? null);

                    $orderClass = new Order();
                    $packageClass->setOrders($orderClass);
                    $orderClass->setNumber($order['salesRecordNumber'] ?? null);
                    $orderClass->setConsigneeName($order['buyerName'] ?? null);
                    $orderClass->setConsigneeMobilePhone($order['buyerMobile'] ?? null);
                    $orderClass->setConsigneeTel($order['buyerPhone'] ?? null);
                    $orderClass->setCountry($order['buyerCountry'] ?? null);
                    $orderClass->setConsigneeState(isset($order['buyerState']) ? $order['buyerState'] : '');
                    $orderClass->setConsigneeCity(isset($order['buyerCity']) ? $order['buyerCity'] : '');
                    $orderClass->setConsigneeAddress1(isset($order['receiveAddress']) ? $order['receiveAddress'] : '');
                    $orderClass->setConsigneePostcode(isset($order['postalCode']) ? $order['postalCode'] : '');
                    $orderClass->setTotalAmount(isset($order['orderAmount']) ? $order['orderAmount'] : '');
                    $orderClass->setPlatformId($shop->getPlatformId());
                    $orderClass->setShopId($shop->getId());
                    $orderClass->setPlaceOrderAt(isset($order['saleTime']) ? strtotime($order['saleTime']) : '');
                    $orderClass->setPaymentAt(isset($order['paidTime']) ? strtotime($order['paidTime']) : '');
                    foreach ($order['orderDetails'] as $item) {
                        $orderItemClass = new OrderItem();
                        $orderItemClass->setProductName($item['webstore_sku']);
                        $orderItemClass->setQuantity($item['quantity']);
                        $orderItemClass->setSku($item['webstore_sku']);
                        $orderItemClass->setSalePrice($item['transaction_price']);
                        $orderItemClass->setImage('');
                        if ($order['goodsInfo']['tongToolGoodsInfoList']) {
//                            $tongtoolSku = $item['goodsMatchedSku'];
                            foreach ($order['goodsInfo']['tongToolGoodsInfoList'] as $info) {
                                if ($info['goodsSku'] == $orderItemClass->getSku()) {
                                    $orderItemClass->setProductName($info['productName']);
                                    if ($img = $info['goodsImageGroupId']) {
                                        if (strncasecmp($img, 'http', 4) !== 0) {
                                            $img = "https://erp112.xxx.com/file" . $img;
                                        }
                                        $imagePath = $this->downloadImage($img, $orderItemClass->getSku());
                                        if ($imagePath) {
                                            $orderItemClass->setImage($imagePath);
                                        }
                                    }
                                    break;
                                }
                            }
                        }

                        $orderClass->setItem($orderItemClass);
                    }
                    $packages[] = $packageClass;
                }
                if (!isset($rawOrders[$pageSize - 1])) {
                    break; // 如果订单数小于 pageSize, 停止翻页
                }
            }
            $pageNo += 1;
        }

        return $packages;
    }

    /**
     * @param array $identity
     * @param Shop|null $shop
     * @param DateTime|null $beginDate
     * @param DateTime|null $endDate
     * @return array
     * @throws \yii\base\Exception
     */
    public function getPackages(array $identity, Shop $shop = null, DateTime $beginDate = null, Datetime $endDate = null): array
    {
        $identity = $identity['tongtool'];
        $this->getToken($identity);
        $now = time();
        $sign = md5('app_token' . $this->app_token . 'timestamp' . $now . $identity['app_secret']);
        $auth = 'app_token=' . $this->app_token . '&timestamp=' . $now . '&sign=' . $sign;
        $shopSign = $shop->getSign();
        if ($shopSign) {
            if (strpos($shopSign, '|') === false) {
                $this->accountCode = $shopSign;
            } else {
                list($this->accountCode, $this->account) = explode('|', $shopSign);
            }
        }
        $maxSeconds = 12;
        $now = time();
        $packages = $this->getSelfPackages($auth, $shop, $beginDate, $endDate);

        $seconds = time() - $now;
        if ($seconds < $maxSeconds) {
            $seconds = $maxSeconds - $seconds;
            Console::stdout("Please waiting {$seconds} seconds..." . PHP_EOL);
            sleep($seconds);
        }
        $fbaPackages = $this->getFbaPackages($auth, $shop, $beginDate, $endDate);

        return array_merge($fbaPackages, $packages);
    }

}

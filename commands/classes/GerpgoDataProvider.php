<?php

namespace app\commands\classes;

use app\jobs\GerpgoCookieJob;
use app\models\Constant;
use DateTime;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use InvalidArgumentException;
use Yii;
use yii\helpers\ArrayHelper;
use yii\helpers\Console;

/**
 * 数据处理
 *
 * @package app\command\classes
 */
class GerpgoDataProvider extends ThirdPartyDataProvider
{

    private $_response;

    private static $statusOptions = [
        0 => Constant::THIRD_PARTY_PLATFORM_GERPGO_ORDER_STATUS_WEI_FU_KUAN,
        1 => Constant::THIRD_PARTY_PLATFORM_GERPGO_ORDER_STATUS_YI_FA_HUO,
        2 => Constant::THIRD_PARTY_PLATFORM_GERPGO_ORDER_STATUS_JIAN_HUO_ZHONG,
        3 => Constant::THIRD_PARTY_PLATFORM_GERPGO_ORDER_STATUS_YI_FA_HUO,
        4 => Constant::THIRD_PARTY_PLATFORM_GERPGO_ORDER_STATUS_YI_QU_XIAO,
        5 => Constant::THIRD_PARTY_PLATFORM_GERPGO_ORDER_STATUS_YU_DING,
        6 => Constant::THIRD_PARTY_PLATFORM_GERPGO_ORDER_STATUS_BU_FENG_FA_HUO,
        7 => Constant::THIRD_PARTY_PLATFORM_GERPGO_ORDER_STATUS_UNKNOWN,
    ];

    /**
     * Get HTTP client
     *
     * @param null $cookies
     * @return Client
     * @throws Exception
     */
    private function getClient($cookies = null)
    {
        $config = [
            'headers' => [
                'user-agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/83.0.4103.97 Safari/537.36',
                'host' => '127.0.0.1',
                'Origin' => 'http://xxx.com',
                'Referer' => 'http://xxx.com/sale/order',
                'Cookie' => $cookies,
            ],
        ];

        return new Client($config);
    }

    /**
     * 获取订单数据
     *
     * @param array $identity
     * @param Shop|null $shop
     * @param DateTime $datetime
     * @return array
     * @throws \Throwable
     */
    public function getOrders(array $identity, Shop $shop = null, DateTime $datetime = null): array
    {
        return [];
    }

    private function readResponse($key, $default = null)
    {
        return $this->_response ? ArrayHelper::getValue($this->_response, $key, $default) : $default;
    }

    /**
     * 获取包裹列表
     *
     * @param array $identity
     * @param Shop|null $shop
     * @param DateTime|null $beginDate
     * @param DateTime|null $endDate
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \yii\db\Exception
     * @throws Exception
     */
    public function getPackages(array $identity, Shop $shop = null, DateTime $beginDate = null, Datetime $endDate = null): array
    {
        $packages = [];
        if (!isset($identity['gerpgo']['cookie'])) {
            throw new InvalidArgumentException("The gerpgo cookie is required!");
        } else {
            $page = 1;
            $client = $this->getClient($identity['gerpgo']['cookie']);
            $url = "http://xxx.com/v2/channel/order?pagesize=20&sort=purchaseDate&order=descend";
            $beginDate->modify('-1 days'); // 时区问题，多获取前一天的数据
            $orderCreateStart = $beginDate->format('Y-m-d');
            $orderCreateEnd = $endDate->format("Y-m-d");

            // 筛选店铺
            if ($sign = $shop->getSign()) {
                $url .= '&marketId=' . $sign;
            } else {
                throw new InvalidArgumentException("Invalid `Third party sign value` in #" . $shop->getId() . ' shop.');
            }

            $url .= "&startDate=" . $orderCreateStart . "&endDate=" . $orderCreateEnd;
            try {
                $response = $client->request('GET', $url . '&page=' . $page);
                $responseContent = $response->getBody()->getContents();
                $this->_response = json_decode($responseContent, true);
                if ($this->readResponse('code') == 0) {
                    $totalCount = $this->readResponse('data.total');
                    $pageSize = $this->readResponse('data.pagesize');
                    $totalPage = (int) (($totalCount + $pageSize - 1) / $pageSize);
                    for ($page = 1; $page <= $totalPage; $page++) {
                        if ($page != 1) {
                            $response = $client->request('GET', $url . '&page=' . $page);

                            $this->_response = json_decode($response->getBody()->getContents(), true);
                        }
                        foreach ($this->readResponse('data.rows', []) as $row) {
                            $packageClass = new Package();
                            $packageClass->setShopId($shop->getId());
                            $orderClass = new Order();
                            $orderClass->setShopId($shop->getId());
                            $packageClass->setOrders($orderClass);
                            $orderClass->setNumber($row['orderId']);
                            $orderClass->setConsigneeName($row['buyerName']);
                            $orderClass->setConsigneeMobilePhone($row['addressPhone']);
                            $orderClass->setCountry($row['addressCountrycode']);
                            $orderClass->setConsigneeState($row['addressStateorregion']);
                            $orderClass->setConsigneeCity($row['addressCity']);
                            $orderClass->setConsigneeAddress1($row['addressLine1']);
                            $orderClass->setConsigneeAddress2($row['addressLine2']);
                            $orderClass->setConsigneePostcode($row['addressPostalcode']);
                            $orderClass->setTotalAmount($row['orderTotal']);
                            $orderClass->setPlaceOrderAt(strtotime($row['purchaseDate']));
                            $orderClass->setPaymentAt(strtotime($row['purchaseDate'])); // ？
                            $orderClass->setPlatformId($shop->getPlatformId());
                            $orderClass->setThirdPartyPlatformStatus(self::$statusOptions[$row['orderStatus']] ?? Constant::THIRD_PARTY_PLATFORM_GERPGO_ORDER_STATUS_UNKNOWN);
                            foreach ($row['itemVos'] as $item) {
                                $orderItemClass = new OrderItem();
                                $orderItemClass->setKey($item['itemId']);
                                $orderItemClass->setSku($item['sku']);
                                $productName = trim($item['productName']);
                                $productName || $productName = $orderItemClass->getSku();
                                $orderItemClass->setProductName($productName);
                                $orderItemClass->setQuantity($item['quantityOrdered']);
                                $orderItemClass->setSalePrice($item['itemPrice']);
                                if ($item['smallImageUrl']) {
                                    $image = $this->downloadImage($item['smallImageUrl'], $item['sku']);
                                    $orderItemClass->setImage($image);
                                }
                                $orderClass->setItem($orderItemClass);
                            }
                            $packages[] = $packageClass;
                        }
                    }
                }
            } catch (RequestException $e) {
                $this->_response = null;
                if ($e->hasResponse()) {
                    if ($e->getResponse()->getStatusCode() == 401) {
                        // Cookie 失效
                        Console::stderr("[ 401 ]: " . $e->getMessage() . PHP_EOL);
                        $id = \Yii::$app->getDb()->createCommand('SELECT [[third_party_authentication_id]] FROM {{%g_shop}} WHERE [[id]] = :id', [':id' => $shop->getId()])->queryScalar();
                        if ($id) {
                            Yii::$app->queue->push(new GerpgoCookieJob([
                                'id' => $id,
                            ]));
                        }
                    }
                } else {
                    Console::stderr("Error：" . $e->getMessage() . PHP_EOL);
                }
            } catch (\Exception $e) {
                $this->_response = null;
                Console::stderr("未知错误：" . $e->getMessage() . PHP_EOL);
            }
        }

        return $packages;
    }

}

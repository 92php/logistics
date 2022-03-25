<?php

namespace app\commands\classes;

use app\models\Constant;
use DateTime;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;
use InvalidArgumentException;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\DomCrawler\Crawler;
use yii\helpers\Console;
use function Symfony\Component\String\u;

/**
 * 数据处理
 *
 * @package app\command\classes
 */
class DxmDataProvider extends ThirdPartyDataProvider
{

    /**
     * @var array $orderStatusOptions 订单状态映射
     */
    private $orderStatusOptions = [
        'ordered' => Constant::THIRD_PARTY_PLATFORM_DXM_ORDER_STATUS_WEI_FU_KUAN, // 未付款
        'risk_control' => Constant::THIRD_PARTY_PLATFORM_DXM_ORDER_STATUS_FENG_KONG_ZHONG, // 风控中
        'paid' => Constant::THIRD_PARTY_PLATFORM_DXM_ORDER_STATUS_DAI_SHEN_HE, // 已付款/待审核
        'approved' => Constant::THIRD_PARTY_PLATFORM_DXM_ORDER_STATUS_DAI_CHU_LI, // 已审核/待处理
        'processed' => Constant::THIRD_PARTY_PLATFORM_DXM_ORDER_STATUS_YI_CHU_LI, // 已处理
        'allocated_has' => Constant::THIRD_PARTY_PLATFORM_DXM_ORDER_STATUS_DAI_DA_DANG_YOU_HUO, // 待打单（有货）
        'allocated_out' => Constant::THIRD_PARTY_PLATFORM_DXM_ORDER_STATUS_DAI_DA_DANG_QUE_HUO, // 待打单（缺货）
        'allocated_exception' => Constant::THIRD_PARTY_PLATFORM_DXM_ORDER_STATUS_DAI_DA_DANG_YOU_YI_CHANG, // 待打单（有异常）
        'shipped' => Constant::THIRD_PARTY_PLATFORM_DXM_ORDER_STATUS_YI_JIAO_YUN, // 已发货/已交运
        'refound' => Constant::THIRD_PARTY_PLATFORM_DXM_ORDER_STATUS_TUI_KUAN, // 已退款
        'ignore' => Constant::THIRD_PARTY_PLATFORM_DXM_ORDER_STATUS_YI_HU_LUE, // 已忽略
        'finish' => Constant::THIRD_PARTY_PLATFORM_DXM_ORDER_STATUS_YI_WAN_CHENG, // 已完成
    ];
    /**
     * @var array $orderStatusOptions 店小秘包裹状态映射
     */
    private $packageStatusOptions = [
        'ordered' => Constant::THIRD_PARTY_PLATFORM_DXM_PACKAGE_STATUS_WEI_FU_KUAN, // 未付款
        'risk_control' => Constant::THIRD_PARTY_PLATFORM_DXM_PACKAGE_STATUS_FENG_KONG_ZHONG, // 风控中
        'paid' => Constant::THIRD_PARTY_PLATFORM_DXM_PACKAGE_STATUS_DAI_SHEN_HE, // 已付款/待审核
        'approved' => Constant::THIRD_PARTY_PLATFORM_DXM_PACKAGE_STATUS_DAI_CHU_LI, // 已审核/待处理
        'processed' => Constant::THIRD_PARTY_PLATFORM_DXM_PACKAGE_STATUS_YI_CHU_LI, // 已处理
        'allocated_has' => Constant::THIRD_PARTY_PLATFORM_DXM_PACKAGE_STATUS_DAI_DA_DANG_YOU_HUO, // 待打单（有货）
        'allocated_out' => Constant::THIRD_PARTY_PLATFORM_DXM_PACKAGE_STATUS_DAI_DA_DANG_QUE_HUO, // 待打单（缺货）
        'allocated_exception' => Constant::THIRD_PARTY_PLATFORM_DXM_PACKAGE_STATUS_DAI_DA_DANG_YOU_YI_CHANG, // 待打单（有异常）
        'shipped' => Constant::THIRD_PARTY_PLATFORM_DXM_PACKAGE_STATUS_YI_JIAO_YUN, // 已发货/已交运
        'refound' => Constant::THIRD_PARTY_PLATFORM_DXM_PACKAGE_STATUS_TUI_KUAN, // 已退款
        'ignore' => Constant::THIRD_PARTY_PLATFORM_DXM_PACKAGE_STATUS_YI_HU_LUE, // 已忽略
        'finish' => Constant::THIRD_PARTY_PLATFORM_DXM_PACKAGE_STATUS_YI_WAN_CHENG, // 已完成
    ];

    /**
     * 定制信息解析
     *
     * @param $html
     * @return array
     */
    private function parseExtend($html)
    {
        $json = [
            'names' => [],
            'color' => '',
            'material' => '',
            'size' => '',
            'giftBox' => false,
            'beads' => 0,
            'image' => '',
            'other' => [],
            'raw' => [],
        ];
        $variantsOther = [];
        $crawler = new Crawler();
        $crawler->addHtmlContent($html);
        $crawler->filterXPath('//p')->each(function (Crawler $node) use (&$json, &$variantsOther) {
            $s = u($node->text())->trim();
            $delimiter = '：';
            if (!$s->isEmpty() && !$s->startsWith("_") && $s->containsAny($delimiter)) {
                list($key, $value) = $s->split($delimiter, 2);
                $key = $key->trim()->lower();
                $value = $value->trim();
                $json['raw'][$key->toString()] = $value->toString();
                if ($key->containsAny(['giftbox', 'gift box'])) {
                    $json['giftBox'] = true;
                } elseif ($key->containsAny(['number', 'number of', '#'])) {
                    $a = $value->match('/([\d])+/');
                    isset($a[1]) && $json['beads'] = $a['1'];
                } elseif ($key->containsAny(['please write', 'name', 'charm', 'inscription', 'initial of star']) && !$key->containsAny([
                        'add leaf charm',
                        'add leaf charms',
                    ])) {
                    $json['names'] = array_merge($json['names'], array_map('trim', $value->split(",")));
                } elseif ($key->containsAny(['chain length'])) {
                    // select chain length
                    $json['size'] = $value->toString();
                } elseif ($key->containsAny(['upload', 'photo'])) {
                    $imgPath = $this->downloadImage($value->toString(), md5($value->toString()));
                    $imgPath || $imgPath = $value->toString();
                    $json['image'] = $imgPath;
                } elseif ($key->containsAny(['variants'])) {
                    $variants = [];
                    $materials = [];
                    $colors = [];
                    $sizes = [];
                    foreach ($value->split('/') as $variant) {
                        $v = u($variant)->trim();
                        $variant = $v->toString();
                        $variants[] = $variant;
                        $v = $v->lower();

                        if ($v->containsAny(['beads', 'bead'])) {
                            $a = $value->match('/([\d])+/');
                            isset($a[1]) && $json['beads'] = $a['1'];
                        } elseif ($v->containsAny(['sterling silver', 'silver plat', 'k gold'])) {
                            $materials[] = $variant;
                        } elseif ($v->containsAny(['size', 'cm', 'inch', 'adjustable', 'diameter', "''"])) {
                            $sizes[] = $variant;
                        } elseif ($v->containsAny(['silver', 'gold', 'black'])) {
                            $colors[] = $variant;
                        } else {
                            $variantsOther = [$v->toString()];
                        }
                    }
                    $materials && $json['material'] = implode(' / ', $materials);
                    $colors && $json['color'] = implode(' / ', $colors);
                    if ($sizes) {
                        $s = $json['size'];
                        $s && $s .= ' / ';
                        $json['size'] = $s . implode(' / ', $sizes);
                    }
                } else {
                    $variantsOther[] = $key->toString() . ':' . $value->toString();
                }
            }
        });
        if (!$json['beads'] && $json['names']) {
            $json['beads'] = count($json['names']);
        }
        $json['other'] = $variantsOther;

        return $json;
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
        $orders = [];
        if (!isset($identity['dianxiaomi']['cookie'])) {
            throw new InvalidArgumentException("The xxx cookie is required!");
        } else {
            $client = $this->getClient($identity['dianxiaomi']['cookie']);
            $url = "https://www.xxx.com/package/advancedSearch.htm?pageSize=10&state=&isOversea=-1&isVoided=-1&isRemoved=-1&orderId=&packageNum=&buyerAccount=&contactName=&batchNum=&platform=&contentStr=&searchTypeStr=&authId=-1&country=&shippedStart=&shippedEnd=&storageId=0&productStatus=&priceStart=0.0&priceEnd=0.0&productCountStart=0&timeOut=0&productCountEnd=0&isPrintMd=-1&isPrintJh=-1&isHasOrderComment=-1&isHasOrderMessage=-1&isHasPickComment=-1&commitPlatform=&isGreen=0&isYellow=0&isOrange=0&isRed=0&isViolet=0&isBlue=0&cornflowerBlue=0&pink=0&teal=0&turquoise=0&history=&orderField=order_create_time&isSearch=&isMerge=0&isSplit=0&isReShip=0&isRefund=0&platformSku=&productSku=";
            $orderCreateStart = $orderCreateEnd = $datetime->format('Y-m-d');
            // 筛选店铺
            if ($sign = $shop->getSign()) {
                $url .= '&shopId=' . $sign;
            } else {
                throw new InvalidArgumentException("Invalid `Third party sign value` in #" . $shop->getId() . ' shop.');
            }

            $url .= "&orderCreateStart=" . $orderCreateStart . "&orderCreateEnd=" . $orderCreateEnd;
            $pageNo = 1;
            while (true) {
                // 翻页获取
                $pageOrders = [];
                $errorMessages = [];
                $detail[] = [];
                if (isset($totalPage) && $pageNo > $totalPage) {
                    $totalPage = null;
                    break;
                }
                $response = $client->request('GET', $url . '&pageNo=' . $pageNo);
                if ($response->getStatusCode() == '200') {
                    $html = $response->getBody()->getContents();
                    $crawler = new Crawler();
                    $crawler->addHtmlContent($html);
                    Console::stdout("> Page #$pageNo" . PHP_EOL);

                    try {
                        if (!isset($totalPage)) {
                            $totalPage = (int) $crawler->filterXPath('//input[@id="totalPage"]')->attr('value');
                        }
                        $crawler->filterXPath('//*[@id="orderListTable"]/tbody/tr[not(@class)]')->each(function (Crawler $node) use (&$pageOrders, $client, $shop) {
                            $orderClass = new Order();
                            $orderClass->setShopId($shop->getId());
                            $packageClass = new Package();
                            $packageClass->setShopId($shop->getId());
                            $subCrawler = $node->filterXPath('node()/td[2]');
                            if ($subCrawler->count()) {
                                $s = u($subCrawler->text())->trim()->match('/.* ([\d\.]+).*/');
                                isset($s[1]) && $orderClass->setTotalAmount($s[1]);
                            }

                            $subCrawler = $node->filterXPath('node()/td[4]/a');
                            if ($subCrawler->count()) {
                                $orderClass->setNumber(u($subCrawler->text())->trim()->toString());
                            }

                            $subCrawler = $node->filterXPath('node()/td[5]');
                            if ($subCrawler->count()) {
                                foreach (explode('<br>', $subCrawler->html()) as $s) {
                                    try {
                                        $s = u($s)->trim();
                                        if ($s->containsAny('付款')) {
                                            $ss = explode('：', $s->toString());
                                            isset($ss[1]) && $orderClass->setPaymentAt((new DateTime($ss[1]))->getTimestamp());
                                        } elseif ($s->containsAny('下单')) {
                                            $ss = explode('：', $s->toString());
                                            isset($ss[1]) && $orderClass->setPlaceOrderAt((new DateTime($ss[1]))->getTimestamp());
                                        }
                                    } catch (\Exception $e) {
                                        Console::stderr(('日期转换失败！' . PHP_EOL));
                                    }
                                }
                            }

                            $subCrawler = $node->filterXPath('node()/td[6]/span');
                            if ($subCrawler->count()) {
                                $packageClass->setLogisticsLineName(u($subCrawler->text())->trim());
                            }

                            $subCrawler = $node->filterXPath('node()/td[2]/p');
                            if ($subCrawler->count()) {
                                $packageClass->setKey(trim($subCrawler->attr('id'), 'p_'));
                            }

                            $node->filterXPath('node()/td[1]/table/tr')->each(function (Crawler $productNode, $i) use (&$orderClass, &$packageClass, $client) {
                                $orderItemClass = new OrderItem();

                                $subCrawler = $productNode->filterXPath('node()/td[1]//input[@pid]');
                                if ($subCrawler->count()) {
                                    $orderItemClass->setKey($subCrawler->attr('pid'));
                                }
                                if (empty($packageClass->getKey())) {
                                    $subCrawler = $productNode->filterXPath('node()/td[1]//input[@packageid]');
                                    if ($subCrawler->count()) {
                                        $packageClass->setKey($subCrawler->attr('packageid'));
                                    }
                                }

                                $subCrawler = $productNode->filterXPath('node()/td[1]//input[@displaysku]');
                                if ($subCrawler->count()) {
                                    $orderItemClass->setSku($subCrawler->attr('displaysku'));
                                }

                                $subCrawler = $productNode->filterXPath('node()//img[@data-order]');
                                if ($subCrawler->count()) {
                                    if (($img = $subCrawler->attr("data-order")) &&
                                        !empty($orderItemClass->getSku()) &&
                                        ($imgPath = $this->downloadImage($img, $orderItemClass->getSku()))
                                    ) {
                                        $orderItemClass->setImage($imgPath);
                                    }
                                }

                                $subCrawler = $productNode->filterXPath('node()/td[2]/div/p/a');
                                if ($subCrawler->count()) {
                                    $orderItemClass->setProductName($subCrawler->text());
                                }

                                $subCrawler = $productNode->filterXPath('node()/td[2]/div/p/span');
                                if ($subCrawler->count()) {
                                    $orderItemClass->setQuantity($subCrawler->text());
                                }

                                $subCrawler = $productNode->filterXPath('node()/td[2]/div/p[2]');
                                if ($subCrawler->count()) {
                                    $ss = u($subCrawler->text())->trim()->split(' ');
                                    isset($ss[1]) && $orderItemClass->setSalePrice(floatval($ss[1]->toString()));
                                }

                                // 定制信息
                                $subCrawler = $productNode->filterXPath('node()/td[2]');
                                $orderItemClass->setExtend($this->parseExtend($subCrawler->count() ? $subCrawler->html() : ''));
                                $orderClass->setItem($orderItemClass);
                            });
                            $orderClass->setPackage($packageClass);
                            $pageOrders[] = $orderClass;
                        });

                        // 对每一个 packageId 请求详情页面
                        $packageIds = $crawler->filterXPath('//input[@id="orderIdsStr"]')->attr('value');
                        $packageIds = array_unique(array_filter(explode(';', $packageIds)));
                        $promises = [];
                        foreach ($packageIds as $id) {
                            Console::stdout(' > Trying get ' . $id . " package detail." . PHP_EOL);
                            $promises[$id] = $client->postAsync('https://www.xxx.com/package/detail.htm', [
                                'form_params' => [
                                    'packageId' => $id,
                                ],
                            ]);
                        }
                        $results = $promises ? \GuzzleHttp\Promise\unwrap($promises) : [];
                        unset($orderClass);
                        foreach ($results as $key => $result) {
                            /* @var $result ResponseInterface */
                            if ($result->getStatusCode() == '200') {
                                foreach ($pageOrders as $i => $orderClass) {
                                    /* @var $orderClass Order */
                                    $packageClass = $orderClass->getPackage();
                                    if ($packageClass->getKey() == $key) {
                                        $detailCrawler = new Crawler();
                                        $detailCrawler->addHtmlContent($result->getBody());

                                        $consigneeName = $detailCrawler->filterXPath('//input[@id="detailContact"]');
                                        $consigneeName = $consigneeName->count() ? $consigneeName->attr('value') : '';

                                        $packageState = $detailCrawler->filterXPath('//input[@id="detailPackageState"]');
                                        $packageState = $packageState->count() ? $packageState->attr('value') : '';

                                        $postCode = $detailCrawler->filterXPath('//input[@id="detailZip"]');
                                        $postCode = $postCode->count() ? $postCode->attr('value') : '';

                                        $city = $detailCrawler->filterXPath('//input[@id="detailCity"]');
                                        $city = $city->count() ? $city->attr('value') : '';

                                        $province = $detailCrawler->filterXPath('//input[@id="detailProvince"]');
                                        $province = $province->count() ? $province->attr('value') : '';

                                        $phone = $detailCrawler->filterXPath('//input[@id="detailPhone"]');
                                        $phone = $phone->count() ? $phone->attr('value') : '';

                                        $mobilePhone = $detailCrawler->filterXPath('//input[@id="detailMobile"]');
                                        $mobilePhone = $mobilePhone->count() ? $mobilePhone->attr('value') : '';

                                        $address1 = $detailCrawler->filterXPath('//input[@id="detailAddress1"]');
                                        $address1 = $address1->count() ? $address1->attr('value') : '';

                                        $address2 = $detailCrawler->filterXPath('//input[@id="detailAddress2"]');
                                        $address2 = $address2->count() ? $address2->attr('value') : '';

                                        $detailAddressCountry1 = $detailCrawler->filterXPath('//input[@id="detailAddressCountry1"]');
                                        $detailAddressCountry1 = $detailAddressCountry1->count() ? $detailAddressCountry1->attr('value') : '';

                                        $packageNumber = $detailCrawler->filterXPath('//span[@id="dxmPackageNumDetailSpan"]');
                                        $packageNumber = $packageNumber->count() ? trim($packageNumber->text()) : 0;

                                        $orderClass->setConsigneeCity($city);
                                        $orderClass->setConsigneePostcode($postCode);
                                        $orderClass->setConsigneeState($province);
                                        $orderClass->setCountry($detailAddressCountry1);
                                        $orderClass->setConsigneeMobilePhone($mobilePhone);
                                        $orderClass->setConsigneeTel($phone);
                                        $orderClass->setConsigneeName($consigneeName);
                                        $orderClass->setConsigneeAddress1($address1);
                                        $orderClass->setConsigneeAddress2($address2);
                                        // @todo 考虑拆单和补单，同一个订单分两个包裹，处于不同的状态
                                        $platformStatus = $this->orderStatusOptions[$packageState] ?? Constant::THIRD_PARTY_PLATFORM_DXM_ORDER_STATUS_UNKNOWN;
                                        $orderClass->setThirdPartyPlatformStatus($platformStatus);
                                        $packageStatus = $this->packageStatusOptions[$packageState] ?? Constant::THIRD_PARTY_PLATFORM_DXM_PACKAGE_STATUS_UNKNOWN;
                                        $packageClass->setThirdPartyPlatformStatus($packageStatus);
                                        $packageClass->setNumber($packageNumber);
                                        if (empty($packageClass->getLogisticsLineName())) {
                                            $lineName = null;
                                            foreach ($pageOrders as $o) {
                                                /* @var $o Order */
                                                $pkg = $o->getPackage();
                                                if ($pkg->getKey() == $key && !empty($pkg->getLogisticsLineName())) {
                                                    $lineName = $pkg->getLogisticsLineName();
                                                    break;
                                                }
                                            }
                                            $lineName && $packageClass->setLogisticsLineName($lineName);
                                        }
                                        $orderClass->setPackage($packageClass);
                                        $pageOrders[$i] = $orderClass;
                                    }
                                }
                            } else {
                                $errorMessages[] = ' > HTTP CODE ERROR:' . $response->getStatusCode();
                            }
                        }
                    } catch (InvalidArgumentException $e) {
                        $errorMessages[] = "> Error:" . $e->getMessage() . "Please update the Cookie!" . PHP_EOL;
                        $errorMessages[] = "> Error : The cookies of shop [" . $shop->getId() . '] maybe invalid' . PHP_EOL;
                        break;
                    } catch (\Exception $e) {
                        $errorMessages[] = $e->getFile() . ": Line " . $e->getLine() . " : " . $e->getMessage();
                    }
                } else {
                    $errorMessages[] = "Get page $pageNo return error: " . $response->getStatusCode();
                }

                $orders = array_merge($pageOrders, $orders);
                unset($pageOrders);

                $pageNo += 1;
                if ($errorMessages) {
                    Console::stdout(var_export($errorMessages, true) . PHP_EOL);
                    break;
                }
                sleep(1);
            }

            return $orders;
        }
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
     * @throws \Throwable
     */
    public function getPackages(array $identity, Shop $shop = null, DateTime $beginDate = null, Datetime $endDate = null): array
    {
        $orders = [];
        if (!isset($identity['dianxiaomi']['cookie'])) {
            throw new InvalidArgumentException("The xxx cookie is required!");
        } else {
            $client = $this->getClient($identity['dianxiaomi']['cookie']);
            $url = "https://www.xxx.com/package/advancedSearch.htm?pageSize=200&state=&isOversea=-1&isVoided=-1&isRemoved=-1&orderId=&packageNum=&buyerAccount=&contactName=&batchNum=&platform=&contentStr=&searchTypeStr=&authId=-1&country=&shippedStart=&shippedEnd=&storageId=0&productStatus=&priceStart=0.0&priceEnd=0.0&productCountStart=0&timeOut=0&productCountEnd=0&isPrintMd=-1&isPrintJh=-1&isHasOrderComment=-1&isHasOrderMessage=-1&isHasPickComment=-1&commitPlatform=&isGreen=0&isYellow=0&isOrange=0&isRed=0&isViolet=0&isBlue=0&cornflowerBlue=0&pink=0&teal=0&turquoise=0&history=&orderField=order_create_time&isSearch=&isMerge=0&isSplit=0&isReShip=0&isRefund=0&platformSku=&productSku=";
            $orderCreateStart = $beginDate->format('Y-m-d');
            $orderCreateEnd = $endDate->format("Y-m-d");

            // 筛选店铺
            if ($sign = $shop->getSign()) {
                $url .= '&shopId=' . $sign;
            } else {
                throw new InvalidArgumentException("Invalid `Third party sign value` in #" . $shop->getId() . ' shop.');
            }

            $url .= "&orderCreateStart=" . $orderCreateStart . "&orderCreateEnd=" . $orderCreateEnd;
            $pageNo = 1;
            while (true) {
                // 翻页获取
                $pagePackages = [];
                $errorMessages = [];
                $detail[] = [];
                if (isset($totalPage) && $pageNo > $totalPage) {
                    $totalPage = null;
                    break;
                }
                $response = $client->request('GET', $url . '&pageNo=' . $pageNo);
                if ($response->getStatusCode() == '200') {
                    $html = $response->getBody()->getContents();
                    $crawler = new Crawler();
                    $crawler->addHtmlContent($html);
                    Console::stdout("> Page #$pageNo" . PHP_EOL);

                    try {
                        if (!isset($totalPage)) {
                            $totalPage = (int) $crawler->filterXPath('//input[@id="totalPage"]')->attr('value');
                        }
                        $orderType = \app\modules\admin\modules\g\models\Order::TYPE_NORMAL;
                        $crawler->filterXPath('//*[@id="orderListTable"]/tbody/tr')->each(function (Crawler $node) use (&$pagePackages, $client, $shop, &$orderType) {
                            $className = trim($node->attr('class'));
                            $packageKey = null;
                            if ($className == 'goodsId') {
                                $subCrawler = $node->filterXPath('node()/td[1]/input[@name="packageId"]');
                                if ($subCrawler->count()) {
                                    $packageKey = $subCrawler->attr('value');
                                } else {
                                    throw new Exception("Not found package id.");
                                }

                                $subCrawler = $node->filterXPath('node()/td[1]/span[@class="squareSpan dataTriggerHover"]');
                                if ($subCrawler->count() && ($s = $subCrawler->text())) {
                                    u($s)->collapseWhitespace()->toString() == '补' && $orderType = \app\modules\admin\modules\g\models\Order::TYPE_REISSUE;
                                } else {
                                    $orderType = \app\modules\admin\modules\g\models\Order::TYPE_NORMAL;
                                }
                            } else {
                                // 数据行
                                $subCrawler = $node->filterXPath('node()/td[7]');
                                $payment = false;
                                if ($subCrawler->count()) {
                                    $payment = u($subCrawler->text())->trim()->toString() != '未付款';
                                }
                                if ($payment) {
                                    $subCrawler = $node->filterXPath('node()/td[2]/p');
                                    if ($subCrawler->count()) {
                                        $packageKey = trim($subCrawler->attr('id'), 'p_');
                                    }

                                    $orderClass = new Order();
                                    $orderClass->setType($orderType);
                                    $orderClass->setShopId($shop->getId());
                                    $orderClass->setPlatformId($shop->getPlatformId());

                                    $subCrawler = $node->filterXPath('node()/td[2]');
                                    if ($subCrawler->count()) {
                                        $s = u($subCrawler->text())->trim()->match('/[\d\.]+/');
                                        isset($s[0]) && $orderClass->setTotalAmount($s[0]);
                                    }

                                    $subCrawler = $node->filterXPath('node()/td[4]/a');
                                    if ($subCrawler->count()) {
                                        $orderClass->setNumber(u($subCrawler->text())->trim()->toString());
                                    }

                                    $subCrawler = $node->filterXPath('node()/td[4]/p/span[@class="squareSpan hover-prompt hoverPrompt"]');
                                    if ($subCrawler->count()) {
                                        $orderClass->setRemark(u($subCrawler->attr('data-content'))->trim()->toString());
                                    }

                                    $subCrawler = $node->filterXPath('node()/td[5]');
                                    if ($subCrawler->count()) {
                                        foreach (explode('<br>', $subCrawler->html()) as $s) {
                                            try {
                                                $s = u($s)->trim();
                                                if ($s->containsAny('付款')) {
                                                    $ss = explode('：', $s->toString());
                                                    isset($ss[1]) && $orderClass->setPaymentAt((new DateTime($ss[1]))->getTimestamp());
                                                } elseif ($s->containsAny('下单')) {
                                                    $ss = explode('：', $s->toString());
                                                    isset($ss[1]) && $orderClass->setPlaceOrderAt((new DateTime($ss[1]))->getTimestamp());
                                                }
                                            } catch (\Exception $e) {
                                                Console::stderr(('日期转换失败！' . PHP_EOL));
                                            }
                                        }
                                    }

                                    $node->filterXPath('node()/td[1]/table/tr')->each(function (Crawler $productNode, $i) use (&$orderClass, &$packageKey) {
                                        $orderItemClass = new OrderItem();

                                        $subCrawler = $productNode->filterXPath('node()/td[1]//input[@pid]');
                                        if ($subCrawler->count()) {
                                            $s = u($subCrawler->attr('id'))->trim()->replace('relationListHidden_', '');
                                            $orderItemClass->setKey($s->toString());
                                            $orderItemClass->setPid($subCrawler->attr('pid'));
                                        }

                                        if (empty($packageKey)) {
                                            $subCrawler = $productNode->filterXPath('node()/td[1]//input[@packageid]');
                                            if ($subCrawler->count()) {
                                                $packageKey = $subCrawler->attr('packageid');
                                            }
                                        }

                                        $subCrawler = $productNode->filterXPath('node()/td[1]//input[@displaysku]');
                                        if ($subCrawler->count()) {
                                            $orderItemClass->setSku($subCrawler->attr('displaysku'));
                                        }

                                        $subCrawler = $productNode->filterXPath('node()//img[@data-order]');
                                        if ($subCrawler->count()) {
                                            if (($img = $subCrawler->attr("data-order")) &&
                                                !empty($orderItemClass->getSku()) &&
                                                ($imgPath = $this->downloadImage($img, $orderItemClass->getSku()))
                                            ) {
                                                $orderItemClass->setImage($imgPath);
                                            }
                                        }

                                        $subCrawler = $productNode->filterXPath('node()/td[2]/div/p/a');
                                        if ($subCrawler->count()) {
                                            $orderItemClass->setProductName(u($subCrawler->text())->trim()->toString());
                                        }

                                        $subCrawler = $productNode->filterXPath('node()/td[2]/div/p/span');
                                        if ($subCrawler->count()) {
                                            $orderItemClass->setQuantity($subCrawler->text());
                                        }

                                        $subCrawler = $productNode->filterXPath('node()/td[2]/div/p[2]');
                                        if ($subCrawler->count()) {
                                            $ss = u($subCrawler->text())->trim()->split(' ');
                                            isset($ss[1]) && $orderItemClass->setSalePrice(floatval($ss[1]->toString()));
                                        }

                                        // 定制信息
                                        $subCrawler = $productNode->filterXPath('node()/td[2]');
                                        $orderItemClass->setExtend($this->parseExtend($subCrawler->count() ? $subCrawler->html() : ''));

                                        // 是否忽略
                                        $ignored = false;
                                        $extend = $orderItemClass->getExtend();
                                        $variants = $extend['raw']['variants'] ?? null;
                                        if ($variants == 'checked') {
                                            $ignored = true;
                                        }
                                        $orderItemClass->setIgnored($ignored);

                                        $orderClass->setItem($orderItemClass);
                                    });
                                    $orderItems = [];
                                    foreach ($orderClass->getItems() as $item) {
                                        /* @var $item OrderItem */
                                        if (!$item->getIgnored()) {
                                            $extend = $item->getExtend();
                                            $raw = $extend['raw'];
                                            if (count($raw) == 1 && (isset($raw['variants']) || isset($raw['Variants']))) {
                                                // 只有一个项目
                                                $variants = $raw['variants'] ?? null;
                                                $variants || $variants = $raw['Variants'] ?? null;
                                                if ($variants) {
                                                    $variants = u($variants)->collapseWhitespace()->toString();
                                                    foreach ($orderClass->getItems() as $d) {
                                                        /* @var $d OrderItem */
                                                        $dExtend = $d->getExtend();
                                                        $dRaw = $dExtend['raw'];
                                                        if (count($dRaw) > 1) {
                                                            foreach ($dRaw as $v) {
                                                                $v = u($v)->collapseWhitespace()->toString();
                                                                if ($v == $variants || $v == "DAD+$variants") {
                                                                    $item->setIgnored(true);
                                                                    break 2;
                                                                }
                                                            }
                                                        }
                                                    }
                                                }
                                            }
                                        }
                                        $orderItems[] = $item;
                                    }
                                    $orderClass->setItems($orderItems);
                                    if ($packageKey) {
                                        if (isset($pagePackages[$packageKey])) {
                                            $packageClass = $pagePackages[$packageKey];
                                        } else {
                                            $packageClass = new Package();
                                            $packageClass->setKey($packageKey);
                                            $packageClass->setShopId($shop->getId());
                                        }
                                        $packageClass->setOrders($orderClass);
                                        $pagePackages[$packageKey] = $packageClass;
                                    }
                                }
                            }
                        });

                        // 对每一个 packageId 请求详情页面
                        $packageIds = $crawler->filterXPath('//input[@id="orderIdsStr"]')->attr('value');
                        $packageIds = array_unique(array_filter(explode(';', $packageIds)));
                        $promises = [];
                        foreach ($packageIds as $id) {
                            Console::stdout(' > Trying get ' . $id . " package detail." . PHP_EOL);
                            $promises[$id] = $client->postAsync('https://www.xxx.com/package/detail.htm', [
                                'form_params' => [
                                    'packageId' => $id,
                                ],
                            ]);
                        }
                        $results = $promises ? \GuzzleHttp\Promise\unwrap($promises) : [];
                        unset($orderClass);
                        foreach ($results as $key => $result) {
                            /* @var $result ResponseInterface */
                            if ($result->getStatusCode() == '200') {
                                foreach ($pagePackages as $i => &$pkgClass) {
                                    /* @var $pkgClass Package */
                                    if ($pkgClass->getKey() == $key) {
                                        $detailCrawler = new Crawler();
                                        $detailCrawler->addHtmlContent($result->getBody());

                                        $consigneeName = $detailCrawler->filterXPath('//input[@id="detailContact"]');
                                        $consigneeName = $consigneeName->count() ? $consigneeName->attr('value') : '';

                                        $postCode = $detailCrawler->filterXPath('//input[@id="detailZip"]');
                                        $postCode = $postCode->count() ? $postCode->attr('value') : '';

                                        $city = $detailCrawler->filterXPath('//input[@id="detailCity"]');
                                        $city = $city->count() ? $city->attr('value') : '';

                                        $province = $detailCrawler->filterXPath('//input[@id="detailProvince"]');
                                        $province = $province->count() ? $province->attr('value') : '';

                                        $phone = $detailCrawler->filterXPath('//input[@id="detailPhone"]');
                                        $phone = $phone->count() ? $phone->attr('value') : '';

                                        $mobilePhone = $detailCrawler->filterXPath('//input[@id="detailMobile"]');
                                        $mobilePhone = $mobilePhone->count() ? $mobilePhone->attr('value') : '';

                                        $address1 = $detailCrawler->filterXPath('//input[@id="detailAddress1"]');
                                        $address1 = $address1->count() ? $address1->attr('value') : '';

                                        $address2 = $detailCrawler->filterXPath('//input[@id="detailAddress2"]');
                                        $address2 = $address2->count() ? $address2->attr('value') : '';

                                        $detailAddressCountry1 = $detailCrawler->filterXPath('//input[@id="detailAddressCountry1"]');
                                        $detailAddressCountry1 = $detailAddressCountry1->count() ? $detailAddressCountry1->attr('value') : '';
                                        if (empty($pkgClass->getCountry())) {
                                            $pkgClass->setCountry($detailAddressCountry1);
                                        }

                                        // 包裹信息
                                        $subCrawler = $detailCrawler->filterXPath('//span[@id="dxmPackageNumDetailSpan"]');
                                        if ($subCrawler->count()) {
                                            $pkgClass->setNumber(trim($subCrawler->text()));
                                        }

                                        // 物流公司
                                        $subCrawler = $detailCrawler->filterXPath('//input[@id="detailPackageAgentProvider"]');
                                        if ($subCrawler->count()) {
                                            $pkgClass->setLogisticsLineName(u($subCrawler->attr('value'))->trim()->toString());
                                        }

                                        // 运单号
                                        $subCrawler = $detailCrawler->filterXPath('//input[@id="detailPackageTrackNum"]');
                                        if ($subCrawler->count()) {
                                            $pkgClass->setWaybillNumber(u($subCrawler->attr('value'))->trim()->toString());
                                        }

                                        $subCrawler = $detailCrawler->filterXPath('//input[@id="detailPackageState"]');
                                        if ($subCrawler->count()) {
                                            $packageThirdPartyPlatformStatus = $this->packageStatusOptions[$subCrawler->attr('value')] ?? Constant::THIRD_PARTY_PLATFORM_DXM_PACKAGE_STATUS_UNKNOWN;
                                            $orderThirdPartyPlatformStatus = $this->orderStatusOptions[$subCrawler->attr('value')] ?? Constant::THIRD_PARTY_PLATFORM_DXM_ORDER_STATUS_UNKNOWN;
                                        } else {
                                            $packageThirdPartyPlatformStatus = Constant::THIRD_PARTY_PLATFORM_DXM_PACKAGE_STATUS_UNKNOWN;
                                            $orderThirdPartyPlatformStatus = Constant::THIRD_PARTY_PLATFORM_DXM_ORDER_STATUS_UNKNOWN;
                                        }
                                        $pkgClass->setThirdPartyPlatformStatus($packageThirdPartyPlatformStatus);

                                        foreach ($pkgClass->getOrders() as &$orderClass) {
                                            /* @var $orderClass Order */
                                            $orderClass->setConsigneeCity($city);
                                            $orderClass->setConsigneePostcode($postCode);
                                            $orderClass->setConsigneeState($province);
                                            $orderClass->setCountry($detailAddressCountry1);
                                            $orderClass->setConsigneeMobilePhone($mobilePhone);
                                            $orderClass->setConsigneeTel($phone);
                                            $orderClass->setConsigneeName($consigneeName);
                                            $orderClass->setConsigneeAddress1($address1);
                                            $orderClass->setConsigneeAddress2($address2);
                                            $orderClass->setThirdPartyPlatformStatus($orderThirdPartyPlatformStatus);
                                        }
                                    }
                                }
                            } else {
                                $errorMessages[] = ' > HTTP CODE ERROR:' . $response->getStatusCode();
                            }
                        }
                    } catch (InvalidArgumentException $e) {
                        $errorMessages[] = "> Error:" . $e->getMessage() . "Please update the Cookie!" . PHP_EOL;
                        $errorMessages[] = "> Error : The cookies of shop [" . $shop->getId() . '] maybe invalid' . PHP_EOL;
                        break;
                    } catch (\Exception $e) {
                        $errorMessages[] = $e->getFile() . ": Line " . $e->getLine() . " : " . $e->getMessage();
                    }
                } else {
                    $errorMessages[] = "Get page $pageNo return error: " . $response->getStatusCode();
                }

                $orders = array_merge($pagePackages, $orders);
                unset($pagePackages);

                $pageNo += 1;
                if ($errorMessages) {
                    Console::stdout(var_export($errorMessages, true) . PHP_EOL);
                    break;
                }
            }

            return $orders;
        }
    }

    /**
     * @param $cookie string cookie 字符串
     * @return array cookies 数组
     * @throws Exception
     */
    protected function parseCookies($cookie)
    {
        if (!$cookie) {
            throw new Exception("Read cookie error.");
        }
        $cookies = [];
        foreach (explode(';', $cookie) as $item) {
            try {
                list($key, $value) = explode('=', $item);
                $key = trim($key);
                $value = trim($value);
                $cookies[$key] = $value;
            } catch (\Exception $e) {
                throw $e;
            }
        }

        if (!$cookies) {
            throw new Exception("Cookie maybe has error.");
        }

        return $cookies;
    }

    /**
     * Get dxm client
     *
     * @param null $cookies
     * @return Client
     * @throws Exception
     */
    protected function getClient($cookies = null)
    {
        $config = [
            'headers' => [
                'user-agent' => 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/74.0.3729.157 Safari/537.36',
                'host' => 'www.xxx.com',
                'Referer' => 'https://www.xxx.com/order/index.htm',
            ],
        ];
        if ($cookies) {
            $config['cookies'] = CookieJar::fromArray($this->parseCookies($cookies), 'www.xxx.com');
        }

        return new Client($config);
    }

}

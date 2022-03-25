<?php

namespace app\commands\wl;

use app\commands\Controller;
use app\jobs\DxmCookieJob;
use app\jobs\Package2DxmJob;
use app\models\Constant;
use app\modules\admin\modules\g\models\Order;
use app\modules\admin\modules\g\models\OrderItem;
use app\modules\admin\modules\wuliu\models\Company;
use app\modules\admin\modules\wuliu\models\CompanyLine;
use app\modules\admin\modules\wuliu\models\Package;
use app\modules\api\modules\om\models\OrderItemBusiness;
use DateTime;
use DOMElement;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;
use GuzzleHttp\Promise;
use InvalidArgumentException;
use Psr\Http\Message\ResponseInterface;
use stdClass;
use Symfony\Component\DomCrawler\Crawler;
use Yii;
use yii\helpers\ArrayHelper;
use yii\helpers\FileHelper;
use yii\queue\Queue;
use function Symfony\Component\String\u;

/**
 * 数据同步
 *
 * @package app\commands
 */
class SyncController extends Controller
{

    const STATE_APPROVED = 1; // 待处理状态
    const STATE_ALLOCATED_HAS = 2; // 待打单(有货)状态
    const STATE_SHIPPED = 3; // 发货成功状态

    /**
     * @throws \Exception
     */
    public function init()
    {
        parent::init();
        date_default_timezone_set('Asia/Shanghai');
    }

    /**
     * 包裹状态选项
     *
     * @return array
     */
    public static function statesOptions()
    {
        return [
            self::STATE_APPROVED => 'approved',
            self::STATE_ALLOCATED_HAS => 'allocated_has',
            self::STATE_SHIPPED => 'shipped',
        ];
    }

    /**
     * @param $cookie string cookie 字符串
     * @return array cookies 数组
     * @throws Exception
     */
    protected function getCookiesArray($cookie)
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
     * 同步包裹数据
     *
     * @param null $date 起始付款时间，默认为今天和昨天两天的数据
     * @param int $days 默认为一天你，如果 $date 未提供的话，则会变成两天
     * @param int $state 包裹状态筛选，默认为 1 => 待处理,可指定为 2 => 代打单(有货), 3 => 发货成功
     * @throws \Throwable
     * @throws \yii\db\Exception
     */
    public function actionPackage($date = null, $days = 1, $state = self::STATE_ALLOCATED_HAS)
    {
        $days = abs((int) $days);
        $options = self::statesOptions();
        $state = isset($options[$state]) ? $options[$state] : self::STATE_ALLOCATED_HAS;
        $pageSize = 10;
        $db = Yii::$app->db;
        $errorMessages = [];
        $countries = $db->createCommand("SELECT [[id]], [[abbreviation]] FROM {{%g_country}}")->queryAll();
        $countries = ArrayHelper::map($countries, 'abbreviation', 'id');
        $lines = $db->createCommand("SELECT [[id]], [[name_prefix]], [[name]] FROM {{%wuliu_company_line}}")->queryAll();
        $fnGetLineId = function ($name) use ($lines) {
            $id = 0;
            $name = u($name)->trim()->toString();
            if (!$name) {
                return $id;
            }

            foreach ($lines as $line) {
                $v = $line['name'];
                if ($line['name_prefix']) {
                    $v = "{$line['name_prefix']}-{$v}";
                }
                if ($name == $v) {
                    $id = $line['id'];
                    break;
                }
            }
            if ($id == 0) {
                if (($index = strpos($name, '-')) !== false) {
                    $name = substr($name, $index + 1);
                }
                foreach ($lines as $line) {
                    if ($name == $line['name']) {
                        $id = $line['id'];
                        break;
                    }
                }
            }

            return $id;
        };

        $url = 'https://www.xxx.com/package/advancedSearch.htm?isOversea=-1&isVoided=0&isRemoved=0&orderId=&packageNum=&buyerAccount=&contactName=&batchNum=&shopId=-1&platform=&contentStr=&searchTypeStr=&authId=-1&country=&shippedStart=&shippedEnd=&storageId=0&productStatus=&priceStart=0.0&priceEnd=0.0&productCountStart=0&timeOut=0&productCountEnd=0&isPrintMd=-1&isPrintJh=-1&isHasOrderComment=-1&isHasOrderMessage=-1&isHasPickComment=-1&commitPlatform=&isGreen=0&isYellow=0&isOrange=0&isRed=0&isViolet=0&isBlue=0&cornflowerBlue=0&pink=0&teal=0&turquoise=0&history=&orderField=order_create_time&isSearch=&isMerge=0&isSplit=0&isReShip=0&isRefund=0&globalCollection=-1&platformSku=&productSku=&isDesc=1&state=' . $state;

        $dxmAccounts = $db->createCommand("SELECT [[id]], [[username]], [[cookies]] FROM {{%wuliu_dxm_account}} WHERE [[is_valid]] = " . Constant::BOOLEAN_TRUE . " AND [[cookies]] <> ''")->queryAll();
        foreach ($dxmAccounts as $dxmAccount) {
            $flag = false; // dxm cookie 失效标志
            $client = new Client([
                'headers' => [
                    'user-agent' => 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/74.0.3729.157 Safari/537.36',
                    'host' => 'www.xxx.com',
                    'Referer' => 'https://www.xxx.com/order/index.htm',
                ],
                'cookies' => CookieJar::fromArray($this->getCookiesArray($dxmAccount['cookies']), 'www.dianxiaomi.com'),
            ]);
            try {
                if ($date) {
                    $datetime = new DateTime($date);
                } else {
                    $datetime = new DateTime();
                }
            } catch (\Exception $e) {
                $datetime = new DateTime();
            }
            if (!$date) {
                // 如果未提供日期的话则拉取最近两天的数据（昨天和今天）
                $datetime->modify("-1 days");
                $days++;
            }
            for ($day = 0; $day < $days; $day++) {
                if ($flag || $datetime->getTimestamp() > time()) {
                    break;
                }
                $this->stdout(u(" {$dxmAccount['username']} [ " . $datetime->format('Y-m-d') . ' ] ')->padBoth(80, '#')->toString() . PHP_EOL);
                $orderPayStart = $orderPayEnd = $datetime->format('Y-m-d');  // 筛选日期
                $pageNo = 0;

                while (true) {
                    $pageNo += 1;
                    if (isset($totalPage) && $pageNo > $totalPage) {
                        // 页数达到上限，结束当天的爬取
                        $totalPage = null;
                        break;
                    }
                    $this->stdout(u("▷ {$dxmAccount['username']} [ " . $datetime->format('Y-m-d') . " ] Page #$pageNo ")->padEnd('80', '-')->toString() . PHP_EOL);
                    $packageList = [];
                    $detail = [];
                    $packageIds = [];
                    $response = $client->request('GET', $url . '&pageNo=' . $pageNo . '&pageSize=' . $pageSize . '&orderPayStart=' . $orderPayStart . '&orderPayEnd=' . $orderPayEnd);
                    if ($response->getStatusCode() == '200') {
                        $html = $response->getBody()->getContents();
                        $crawler = new Crawler();
                        $crawler->addHtmlContent($html);
                        try {
                            if (!isset($totalPage)) {
                                $totalPage = (int) $crawler->filterXPath('//input[@id="totalPage"]')->attr('value');
                            }
                            $crawler->filterXPath('//*[@id="orderListTable"]/tbody/tr[not(@class)]')->each(function ($node) use (&$packageList, &$packageIds) {
                                /* @var $node Crawler */
                                $packageId = $node->filterXPath('node()/td[1]//input[@packageid]');
                                $packageId = $packageId->count() ? trim($packageId->attr('packageid')) : 0;
                                if ($packageId) {
                                    $packageList[] = [
                                        'packageId' => $packageId,
                                    ];
                                    $packageIds[] = $packageId;
                                }
                            });

                            // 对每一个 packageId 请求详情页面
                            $packageIds = array_unique(array_filter($packageIds));
                            if ($packageIds) {
                                $this->stdout(' >> Get [ ' . implode(', ', array_map(function ($v) {
                                        return "#$v";
                                    }, $packageIds)) . ' ] packages details...' . PHP_EOL);
                            } else {
                                $this->stdout(" >> Not found packages." . PHP_EOL);
                                continue;
                            }

                            $promises = [];
                            foreach ($packageIds as $id) {
                                $promises[$id] = $client->postAsync('https://www.xxx.com/package/detail.htm', [
                                    'form_params' => [
                                        'packageId' => $id,
                                    ],
                                ]);
                            }
                            $results = $promises ? Promise\unwrap($promises) : [];
                            foreach ($results as $id => $result) {
                                /* @var $result ResponseInterface */
                                if ($result->getStatusCode() == '200') {
                                    $detailCrawler = new Crawler();
                                    $detailCrawler->addHtmlContent($result->getBody()->getContents());

                                    $packageAgentProvider = $detailCrawler->filterXPath('//input[@id="detailPackageAgentProvider"]');
                                    $packageAgentProvider = $packageAgentProvider->count() ? u($packageAgentProvider->attr('value'))->trim()->toString() : '';
                                    if ($lineId = $fnGetLineId($packageAgentProvider)) {
                                        //  不存在该线路名称时,不计入数据
                                        $waybillNumber = $detailCrawler->filterXPath('//input[@id="detailPackageTrackNum"]');
                                        $waybillNumber = $waybillNumber->count() ? $waybillNumber->attr('value') : '';

                                        $country = $detailCrawler->filterXPath('//input[@id="detailAddressCountry1"]');
                                        $country = $country->count() ? $country->attr('value') : '';

                                        $packageNumber = $detailCrawler->filterXPath('//span[@id="dxmPackageNumDetailSpan"]');
                                        $packageNumber = $packageNumber->count() ? $packageNumber->text() : '';

                                        $shopName = $detailCrawler->filterXPath('//div[@class="f-w600"]');
                                        $shopName = $shopName->count() ? str_replace('卖家：', '', trim($shopName->text())) : '';

                                        $platform = $detailCrawler->filterXPath('//input[@id="detailPlatform"]');
                                        $platform = $platform->count() ? $platform->attr('value') : '';

                                        $ids = [];
                                        $orderIds = $detailCrawler->filterXPath('//tr[@class="orderInfoCon pairProInfoBox"]/td/div/a');
                                        if ($orderIds->count()) {
                                            foreach ($orderIds as $orderId) {
                                                /* @var $orderId DOMElement */
                                                $ids[] = trim($orderId->nodeValue);
                                            }
                                            $orderIds = implode(',', array_unique(array_filter($ids)));
                                        } else {
                                            $orderIds = '';
                                        }

                                        $detail[$id] = [
                                            'waybillNumber' => $waybillNumber,
                                            'countryId' => $countries[$country] ?? 0,
                                            'packageNumber' => $packageNumber,
                                            'shopName' => $shopName . '(' . $platform . ')',
                                            'lineId' => $lineId,
                                            'orderIds' => $orderIds,
                                        ];
                                    } else {
                                        //  包裹id xxx 出现不存在的线路名称 xxx
                                        $this->stdout(" > ERROR : Package #{$id} not found line $packageAgentProvider in company lines." . PHP_EOL);
                                    }
                                } else {
                                    $this->stdout(' > HTTP CODE ERROR:' . $response->getStatusCode());
                                }
                            }
                        } catch (InvalidArgumentException $e) {
                            $flag = true;  // cookie 失效
                            $this->stdout("> Error:" . $e->getMessage() . "Please update the cookies of user [" . $dxmAccount['username'] . ']' . PHP_EOL);
                            $errorMessages[] = "> Error:" . $e->getMessage() . "Please update the cookies of user [" . $dxmAccount['username'] . ']' . PHP_EOL;
                            $db->createCommand()->update('{{%wuliu_dxm_account}}', ['is_valid' => Constant::BOOLEAN_FALSE], ['id' => $dxmAccount['id']])->execute();
                            Yii::$app->queue->push(new DxmCookieJob([
                                'id' => $dxmAccount['id'],
                            ]));
                            break;
                        } catch (\Exception $e) {
                            $this->stdout(" > Error: " . $e->getMessage() . PHP_EOL);
                        }
                    } else {
                        $this->stdout(' > Get page $pageNo return error:' . $response->getStatusCode() . PHP_EOL);
                    }

                    //  数据入库
                    foreach ($packageList as $package) {
                        //  合并包裹内数据
                        if (isset($detail[$package['packageId']])) {
                            $package = array_merge($package, $detail[$package['packageId']]);
                            $model = Package::findOne(['package_number' => $package['packageNumber']]);
                            $ignore = false;
                            if ($model === null) {
                                $model = new Package();
                                $model->loadDefaultValues();
                                $payload = [
                                    'package_number' => $package['packageNumber'],
                                    'package_id' => $package['packageId'],
                                    'order_number' => $package['orderIds'],
                                    'waybill_number' => $package['waybillNumber'],
                                    'country_id' => $package['countryId'],
                                    'dxm_account_id' => $dxmAccount['id'],
                                    'shop_name' => $package['shopName'],
                                    'line_id' => $package['lineId'],
                                    'status' => Package::STATUS_PENDING,
                                ];
                            } else {
                                $payload = [
                                    'waybill_number' => $package['waybillNumber'],
                                    'country_id' => $package['countryId'],
                                    'shop_name' => $package['shopName'],
                                    'line_id' => $package['lineId'],
                                ];
                                if ($model->waybill_number == $package['waybillNumber'] &&
                                    $model->country_id == $package['countryId'] &&
                                    $model->shop_name == $package['shopName'] &&
                                    $model->line_id == $package['lineId']
                                ) {
                                    $ignore = true;
                                }
                            }
                            if ($ignore) {
                                $this->stdout(" >> Package #{$package['packageId']}:{$package['packageNumber']} is no change, ignore it." . PHP_EOL);
                            } else {
                                if ($model->load($payload, '') && $model->save()) {
                                    $this->stdout(" >> Package #{$package['packageId']}:{$package['packageNumber']} " . ($model->getIsNewRecord() ? 'insert' : 'update') . " successful." . PHP_EOL);
                                } else {
                                    $this->stdout(" > Error : Package model " . ($model->getIsNewRecord() ? 'insert' : 'update') . " failed!" . PHP_EOL);
                                    foreach ($model->getErrors() as $filed => $errors) {
                                        foreach ($errors as $error) {
                                            $this->stderr(" > $error" . PHP_EOL);
                                        }
                                    }
                                }
                            }
                        } else {
                            $this->stdout(" >> Error : Not found detail of package #{$package['packageId']}" . PHP_EOL);
                        }
                    }
                    sleep(1);
                }
                $datetime->modify("+1 days");
            }
        }
        if ($errorMessages) {
            $this->stdout(str_repeat('=', 80));
            $this->stdout("Found follow errors: " . PHP_EOL);
            foreach ($errorMessages as $message) {
                $this->stdout(" > $message" . PHP_EOL);
            }
        }
        $this->stdout('Done.' . PHP_EOL);
    }

    /**
     * 同步物流商和线路信息
     *
     * @throws \yii\db\Exception
     * @throws Exception
     */
    public function actionCompany()
    {
        $db = Yii::$app->db;
        $cmd = $db->createCommand();
        /**
         * 物流公司运输商简码
         *
         * @see https://www.trackingmore.com/help_article-16-30-cn.html
         */
        $logisticsCompanyCodes = [];
        try {
            $html = <<<EOT
<table class="table_style"><tbody><tr class="firstRow"><td style="word-break: break-all;">运输商简码</td><td style="word-break: break-all;">运输商英文名称</td><td style="word-break: break-all;">	运输商中文名称</td></tr><tr><td>fedex</td><td>Fedex</td><td>Fedex-联邦快递</td></tr><tr><td>dhl</td><td>DHL</td><td>DHL</td></tr><tr><td>ups</td><td>UPS</td><td>UPS</td></tr><tr><td>tnt</td><td>TNT</td><td>TNT</td></tr><tr><td>china-post</td><td>China Post</td><td>中国邮政</td></tr><tr><td>china-ems</td><td>China EMS</td><td>中国 EMS</td></tr><tr><td>hong-kong-post</td><td>Hong Kong Post</td><td>香港邮政</td></tr><tr><td>singapore-post</td><td>Singapore Post</td><td>新加坡邮政(小包)</td></tr><tr><td>swiss-post</td><td>Swiss Post</td><td>瑞士邮政</td></tr><tr><td>usps</td><td>USPS</td><td>美国邮政</td></tr><tr><td>royal-mail</td><td>United Kingdom Royal Mail</td><td>英国皇家邮政</td></tr><tr><td>postnl-parcels</td><td>Netherlands Post - PostNL</td><td>荷兰邮政-PostNL</td></tr><tr><td>canada-post</td><td>Canada Post</td><td>加拿大邮政</td></tr><tr><td>australia-post</td><td>Australia Post</td><td>澳大利亚邮政</td></tr><tr><td>new-zealand-post</td><td>New Zealand Post</td><td>新西兰邮政</td></tr><tr><td>parcel-force</td><td>Parcel Force</td><td>英国邮政parcelforce</td></tr><tr><td>belgium-post</td><td>Bpost</td><td>比利时邮政</td></tr><tr><td>brazil-correios</td><td>Brazil Correios</td><td>巴西邮政</td></tr><tr><td>russian-post</td><td>Russian Post</td><td>俄罗斯邮政</td></tr><tr><td>sweden-posten</td><td>Sweden Posten</td><td>瑞典邮政</td></tr><tr><td>laposte</td><td>La Poste</td><td>法国邮政-La Poste</td></tr><tr><td>poste-italiane</td><td>Poste Italiane</td><td>意大利邮政</td></tr><tr><td>aland-post</td><td>Åland Post</td><td>奥兰群岛芬兰邮政</td></tr><tr><td>afghan-post</td><td>Afghan Post</td><td>阿富汗邮政</td></tr><tr><td>posta-shqiptare</td><td>Albania Post</td><td>阿尔巴尼亚邮政</td></tr><tr><td>andorra-post</td><td>Andorra Post</td><td>安道尔邮政</td></tr><tr><td>antilles-post</td><td>Antilles Post</td><td>荷属安的列斯荷兰邮政</td></tr><tr><td>correo-argentino</td><td>Argentina Post</td><td>阿根廷邮政</td></tr><tr><td>armenia-post</td><td>Armenia Post</td><td>亚美尼亚邮政</td></tr><tr><td>aruba-post</td><td>Aruba Post</td><td>阿鲁巴邮政</td></tr><tr><td>australia-ems</td><td>Australia EMS</td><td>澳大利亚 EMS</td></tr><tr><td>austria-post</td><td>Austrian Post</td><td>奥地利邮政</td></tr><tr><td>azerbaijan-post</td><td>Azerbaijan Post</td><td>阿塞拜疆邮政</td></tr><tr><td>bahrain-post</td><td>Bahrain Post</td><td>巴林邮政</td></tr><tr><td>bangladesh-ems</td><td>Bangladesh EMS</td><td>孟加拉国 EMS</td></tr><tr><td>barbados-post</td><td>Barbados Post</td><td>巴巴多斯邮政</td></tr><tr><td>belpochta</td><td>Belarus Post</td><td>白俄罗斯邮政</td></tr><tr><td>belize-post</td><td>Belize Post</td><td>伯利兹邮政</td></tr><tr><td>benin-post</td><td>Benin Post</td><td>贝宁邮政</td></tr><tr><td>bermuda-post</td><td>Bermuda Post</td><td>百慕大邮政</td></tr><tr><td>bhutan-post</td><td>Bhutan Post</td><td>不丹邮政</td></tr><tr><td>correos-bolivia</td><td>Bolivia Post</td><td>玻利维亚邮政</td></tr><tr><td>bosnia-and-herzegovina-post</td><td>Bosnia And Herzegovina Post</td><td>波黑邮政</td></tr><tr><td>botswana-post</td><td>Botswana Post</td><td>博茨瓦纳邮政</td></tr><tr><td>brunei-post</td><td>Brunei Post</td><td>文莱邮政</td></tr><tr><td>bulgaria-post</td><td>Bulgaria Post</td><td>保加利亚邮政</td></tr><tr><td>sonapost</td><td>Burkina Faso Post</td><td>布基纳法索邮政</td></tr><tr><td>burundi-post</td><td>Burundi Post</td><td>布隆迪邮政</td></tr><tr><td>cambodia-post</td><td>Cambodia Post</td><td>柬埔寨邮政</td></tr><tr><td style="word-break: break-all;">campost</td><td>Cameroon Post</td><td>喀麦隆邮政</td></tr><tr><td>correos-chile</td><td>Correos Chile</td><td>智利邮政</td></tr><tr><td>colombia-post</td><td>Colombia Post</td><td>哥伦比亚邮政</td></tr><tr><td>correos-de-costa-rica</td><td>Costa Rica Post</td><td>哥斯达黎加邮政</td></tr><tr><td>hrvatska-posta</td><td>Croatia Post</td><td>克罗地亚邮政</td></tr><tr><td>cuba-post</td><td>Cuba Post</td><td>古巴邮政</td></tr><tr><td>cyprus-post</td><td>Cyprus Post</td><td>塞浦路斯邮政</td></tr><tr><td>czech-post</td><td>Česká Pošta</td><td>捷克邮政</td></tr><tr><td>denmark-post</td><td>Denmark post</td><td>丹麦邮政</td></tr><tr><td>correos-del-ecuador</td><td>Ecuador Post</td><td>厄瓜多尔邮政</td></tr><tr><td>egypt-post</td><td>Egypt Post</td><td>埃及邮政</td></tr><tr><td>el-salvador-post</td><td>El Salvador Post</td><td>萨尔瓦多邮政</td></tr><tr><td>eritrea-post</td><td>Eritrea Post</td><td>厄立特里亚邮政</td></tr><tr><td>omniva</td><td>Estonia Post</td><td>爱沙尼亚邮政</td></tr><tr><td>ethiopia-post</td><td>Ethiopia Post</td><td>埃塞俄比亚邮政</td></tr><tr><td>faroe-islands-post</td><td>Faroe Islands Post</td><td>法罗群岛邮政</td></tr><tr><td>fiji-post</td><td>Fiji Post</td><td>斐济邮政</td></tr><tr><td>finland-posti</td><td>Finland Post - Posti</td><td>芬兰邮政-Posti</td></tr><tr><td>colissimo</td><td>Colissimo</td><td>法国邮政-Colissimo</td></tr><tr><td>chronopost</td><td>France EMS - Chronopost</td><td>法国 EMS-Chronopost</td></tr><tr><td>georgian-post</td><td>Georgia Post</td><td>格鲁吉亚邮政</td></tr><tr><td>deutsche-post</td><td>Deutsche Post</td><td>德国邮政</td></tr><tr><td>ghana-post</td><td>Ghana Post</td><td>加纳邮政</td></tr><tr><td>gibraltar-post</td><td>Gibraltar &nbsp;Post</td><td>直布罗陀邮政</td></tr><tr><td>greece-post</td><td>ELTA Hellenic Post</td><td>希腊邮政</td></tr><tr><td>tele-post</td><td>Greenland Post</td><td>格陵兰岛邮政</td></tr><tr><td>elcorreo</td><td>Guatemala Post</td><td>危地马拉邮政</td></tr><tr><td>guernsey-post</td><td>Guernsey Post</td><td>根西岛邮政</td></tr><tr><td>magyar-posta</td><td>Magyar Posta</td><td>匈牙利邮政</td></tr><tr><td>iceland-post</td><td>Iceland Post</td><td>冰岛邮政</td></tr><tr><td>india-post</td><td>India Post</td><td>印度邮政</td></tr><tr><td>indonesia-post</td><td>Indonesia Post</td><td>印度尼西亚邮政</td></tr><tr><td>iran-post</td><td>Iran Post</td><td>伊朗邮政</td></tr><tr><td>an-post</td><td>An Post</td><td>爱尔兰邮政</td></tr><tr><td>israel-post</td><td>Israel Post</td><td>以色列邮政</td></tr><tr><td>ivory-coast-ems</td><td>Ivory Coast EMS</td><td>科特迪瓦 EMS</td></tr><tr><td>jamaica-post</td><td>Jamaica Post</td><td>牙买加邮政</td></tr><tr><td>japan-post</td><td>Japan Post</td><td>日本邮政</td></tr><tr><td>jordan-post</td><td>Jordan Post</td><td>约旦邮政</td></tr><tr><td>kazpost</td><td>Kazakhstan Post</td><td>哈萨克斯坦邮政</td></tr><tr><td>kenya-post</td><td>Kenya Post</td><td>肯尼亚邮政</td></tr><tr><td>korea-post</td><td>Korea Post</td><td>韩国邮政</td></tr><tr><td>kyrgyzpost</td><td>Kyrgyzstan Post</td><td>吉尔吉斯斯坦邮政</td></tr><tr><td>laos-post</td><td>Laos Post</td><td>老挝邮政</td></tr><tr><td>latvijas-pasts</td><td>Latvia Post</td><td>拉脱维亚邮政</td></tr><tr><td>liban-post</td><td>Lebanon Post</td><td>黎巴嫩邮政</td></tr><tr><td>lesotho-post</td><td>Lesotho Post</td><td>莱索托邮政</td></tr><tr><td>liechtenstein-post</td><td>Liechtenstein Post</td><td>列支敦士登邮政</td></tr><tr><td>lietuvos-pastas</td><td>Lithuania Post</td><td>立陶宛邮政</td></tr><tr><td>luxembourg-post</td><td>Luxembourg Post</td><td>卢森堡邮政</td></tr><tr><td>macao-post</td><td>Macao Post</td><td>澳门邮政</td></tr><tr><td>macedonia-post</td><td>Macedonia Post</td><td>马其顿邮政</td></tr><tr><td>malaysia-post</td><td>Malaysia Post</td><td>马来西亚邮政</td></tr><tr><td>maldives-post</td><td>Maldives Post</td><td>马尔代夫邮政</td></tr><tr><td>malta-post</td><td>Malta Post</td><td>马耳他邮政</td></tr><tr><td>mauritius-post</td><td>Mauritius Post</td><td>毛里求斯邮政</td></tr><tr><td>correos-mexico</td><td>Mexico Post</td><td>墨西哥邮政</td></tr><tr><td>moldova-post</td><td>Moldova Post</td><td>摩尔多瓦邮政</td></tr><tr><td>la-poste-monaco</td><td>Monaco Post</td><td>摩纳哥邮政</td></tr><tr><td>monaco-ems</td><td>Monaco EMS</td><td>摩纳哥 EMS</td></tr><tr><td>mongol-post</td><td>Mongol Post</td><td>蒙古邮政</td></tr><tr><td>posta-crne-gore</td><td>Montenegro Post</td><td>黑山邮政</td></tr><tr><td>poste-maroc</td><td>Maroc Poste</td><td>摩洛哥邮政</td></tr><tr><td>namibia-post</td><td>Namibia Post</td><td>纳米比亚邮政</td></tr><tr><td>netherlands-post</td><td>Netherlands Post</td><td>荷兰邮政(大包)</td></tr><tr><td>new-caledonia-post</td><td>New Caledonia Post</td><td>新喀里多尼亚邮政</td></tr><tr><td>nicaragua-post</td><td>Nicaragua Post</td><td>尼加拉瓜邮政</td></tr><tr><td>nigeria-post</td><td>Nigeria Post</td><td>尼日利亚邮政</td></tr><tr><td>posten-norge</td><td>Posten Norge</td><td>挪威邮政</td></tr><tr><td>oman-post</td><td>Oman Post</td><td>阿曼邮政</td></tr><tr><td>overseas-territory-fr-ems</td><td>Overseas Territory FR EMS</td><td>海外领地法国 EMS</td></tr><tr><td>overseas-territory-us-post</td><td>Overseas Territory US Post</td><td>海外领地美国邮政</td></tr><tr><td>pakistan-post</td><td>Pakistan Post</td><td>巴基斯坦邮政</td></tr><tr><td>correos-panama</td><td>Panama Post</td><td>巴拿马邮政</td></tr><tr><td>postpng</td><td>Papua New Guinea Post</td><td>巴布亚新几内亚邮政</td></tr><tr><td>correo-paraguayo</td><td>Paraguay Post</td><td>巴拉圭邮政</td></tr><tr><td>serpost</td><td>Serpost</td><td>秘鲁邮政</td></tr><tr><td>phlpost</td><td>Philippines Post</td><td>菲律宾邮政</td></tr><tr><td>poczta-polska</td><td>Poland Post</td><td>波兰邮政</td></tr><tr><td>ctt</td><td>Portugal Post - CTT</td><td>葡萄牙邮政-CTT</td></tr><tr><td>posta-romana</td><td>Poșta Română</td><td>罗马尼亚邮政</td></tr><tr><td>iposita-rwanda</td><td>Rwanda Post</td><td>卢旺达邮政</td></tr><tr><td>saint-lucia-post</td><td>Saint Lucia Post</td><td>圣卢西亚邮政</td></tr><tr><td>svgpost</td><td>Saint Vincent And The Grenadines</td><td>圣文森特和格林纳丁斯</td></tr><tr><td>samoa-post</td><td>Samoa Post</td><td>西萨摩亚邮政</td></tr><tr><td>san-marino-post</td><td>San Marino Post</td><td>圣马力诺邮政</td></tr><tr><td>saudi-post</td><td>Saudi Post</td><td>沙特阿拉伯邮政</td></tr><tr><td>senegal-post</td><td>Senegal Post</td><td>塞内加尔邮政</td></tr><tr><td>serbia-post</td><td>Serbia Post</td><td>塞尔维亚邮政</td></tr><tr><td>seychelles-post</td><td>Seychelles Post</td><td>塞舌尔邮政</td></tr><tr><td>slovakia-post</td><td>Slovakia Post</td><td>斯洛伐克邮政</td></tr><tr><td>slovenia-post</td><td>Slovenia Post</td><td>斯洛文尼亚邮政</td></tr><tr><td>solomon-post</td><td>Solomon Post</td><td>所罗门群岛邮政</td></tr><tr><td>south-africa-post</td><td>South African Post Office</td><td>南非邮政</td></tr><tr><td>correos-spain</td><td>Correos</td><td>西班牙邮政</td></tr><tr><td>sri-lanka-post</td><td>Sri Lanka Post</td><td>斯里兰卡邮政</td></tr><tr><td>sudan-post</td><td>Sudan Post</td><td>苏丹邮政</td></tr><tr><td>syrian-post</td><td>Syrian Post</td><td>叙利亚邮政</td></tr><tr><td>taiwan-post</td><td>Taiwan Post</td><td>台湾邮政</td></tr><tr><td>tanzania-post</td><td>Tanzania Post</td><td>坦桑尼亚邮政</td></tr><tr><td>thailand-post</td><td>Thailand Post</td><td>泰国邮政</td></tr><tr><td>togo-post</td><td>Togo Post</td><td>多哥邮政</td></tr><tr><td>tonga-post</td><td>Tonga Post</td><td>汤加邮政</td></tr><tr><td>tunisia-post</td><td>Tunisia Post</td><td>突尼斯邮政</td></tr><tr><td>turkey-post</td><td>Turkey Post</td><td>土耳其邮政</td></tr><tr><td>uganda-post</td><td>Uganda Post</td><td>乌干达邮政</td></tr><tr><td>ukraine-post</td><td>Ukraine Post</td><td>乌克兰邮政</td></tr><tr><td>ukraine-ems</td><td>Ukraine EMS</td><td>乌克兰 EMS</td></tr><tr><td>emirates-post</td><td>Emirates Post</td><td>阿联酋邮政</td></tr><tr><td>uruguay-post</td><td>Uruguay Post</td><td>乌拉圭邮政</td></tr><tr><td>uzbekistan-post</td><td>Uzbekistan Post</td><td>乌兹别克斯坦邮政</td></tr><tr><td>vanuatu-post</td><td>Vanuatu Post</td><td>瓦努阿图邮政</td></tr><tr><td>vietnam-post</td><td>Vietnam Post</td><td>越南邮政</td></tr><tr><td>yemen-post</td><td>Yemen Post</td><td>也门邮政</td></tr><tr><td>zambia-post</td><td>Zambia Post</td><td>赞比亚邮政</td></tr><tr><td>zimbabwe-post</td><td>Zimbabwe Post</td><td>津巴布韦邮政</td></tr><tr><td>singapore-speedpost</td><td>Singapore Speedpost</td><td>新加坡特快专递</td></tr><tr><td>yanwen</td><td>YANWEN</td><td>燕文</td></tr><tr><td>gls</td><td>GLS</td><td>GLS</td></tr><tr><td>bartolini</td><td>BRT Bartolini</td><td>BRT Bartolini</td></tr><tr><td>dpd</td><td>DPD</td><td>DPD</td></tr><tr><td>sfb2c</td><td>SF International</td><td>顺丰国际</td></tr><tr><td>aramex</td><td>Aramex</td><td>Aramex</td></tr><tr><td>toll</td><td>TOLL</td><td>TOLL</td></tr><tr><td>dhl-germany</td><td>Deutsche Post DHL</td><td>德国DHL</td></tr><tr><td>4px</td><td>4PX</td><td>递四方</td></tr><tr><td>flytexpress</td><td>Flyt Express</td><td>飞特物流</td></tr><tr><td>yunexpress</td><td>Yun Express</td><td>云途物流</td></tr><tr><td>oneworldexpress</td><td>One World Express</td><td>万欧国际</td></tr><tr><td>dhlparcel-nl</td><td>DHL Parcel Netherlands</td><td>荷兰DHL</td></tr><tr><td>dhl-poland</td><td>DHL Poland Domestic</td><td>波兰DHL</td></tr><tr><td>dhl-es</td><td>DHL Spain Domestic</td><td>西班牙DHL</td></tr><tr><td>tnt-it</td><td>TNT Italy</td><td>意大利TNT</td></tr><tr><td>tnt-fr</td><td>TNT France</td><td>法国TNT</td></tr><tr><td>dpd-uk</td><td>DPD UK</td><td>DPD UK</td></tr><tr><td>tnt-uk</td><td>TNT UK</td><td>TNT UK</td></tr><tr><td>gls-italy</td><td>GLS Italy</td><td>意大利GLS</td></tr><tr><td>toll-ipec</td><td>Toll IPEC</td><td>Toll IPEC</td></tr><tr><td>asendia-usa</td><td>Asendia USA</td><td>Asendia USA</td></tr><tr><td>asendia-uk</td><td>Asendia UK</td><td>Asendia UK</td></tr><tr><td>yodel</td><td>Yodel</td><td>Yodel</td></tr><tr><td>asendia-de</td><td>Asendia Germany</td><td>Asendia Germany</td></tr><tr><td>kerry-logistics</td><td>Kerry Express</td><td>嘉里大通物流</td></tr><tr><td>xru</td><td>XRU</td><td>XRU-俄速递</td></tr><tr><td>dpex</td><td>DPEX</td><td>DPEX</td></tr><tr><td>ruston</td><td>Ruston</td><td>Ruston俄速通</td></tr><tr><td>upu</td><td>UPU</td><td>UPU</td></tr><tr><td>bluedart</td><td>Bluedart</td><td>Bluedart</td></tr><tr><td>dtdc</td><td>DTDC</td><td>DTDC</td></tr><tr><td>gojavas</td><td>GoJavas</td><td>GoJavas</td></tr><tr><td>first-flight</td><td>First Flight</td><td>First Flight</td></tr><tr><td>gati-kwe</td><td>Gati-KWE</td><td>Gati-KWE</td></tr><tr><td>rosan</td><td>ROSAN EXPRESS</td><td>中乌融盛速递</td></tr><tr><td>wsgd-logistics</td><td>WSGD Logistics</td><td>WSGD物流</td></tr><tr><td>wishpost</td><td>WishPost</td><td>Wish邮</td></tr><tr><td>sto</td><td>STO Express</td><td>申通快递</td></tr><tr><td>yto</td><td>YTO Express</td><td>圆通快递</td></tr><tr><td>zto</td><td>ZTO Express</td><td>中通快递</td></tr><tr><td>dhlglobalmail</td><td>DHL ECommerce</td><td>DHL电子商务</td></tr><tr><td>dsv</td><td>DSV</td><td>DSV</td></tr><tr><td>echo</td><td>Echo</td><td>Echo</td></tr><tr><td>dpd-ireland</td><td>DPD Ireland</td><td>爱尔兰DPD</td></tr><tr><td>ontrac</td><td>OnTrac</td><td>OnTrac</td></tr><tr><td>purolator</td><td>Purolator</td><td>Purolator</td></tr><tr><td>fastway-nz</td><td>Fastway New Zealand</td><td>新西兰Fastway</td></tr><tr><td>fastway-au</td><td>Fastway Australia</td><td>澳大利亚Fastway</td></tr><tr><td>fastway-ie</td><td>Fastway Ireland</td><td>爱尔兰Fastway</td></tr><tr><td>i-parcel</td><td>I-parcel</td><td>I-parcel</td></tr><tr><td>lasership</td><td>Lasership</td><td>Lasership</td></tr><tr><td>skynetworldwide</td><td>SkyNet Worldwide Express</td><td>SkyNet国际快递</td></tr><tr><td>pfcexpress</td><td>PFC Express</td><td>PFC皇家物流</td></tr><tr><td>nexive</td><td>Nexive</td><td>Nexive</td></tr><tr><td>overnitenet</td><td>Overnite Express</td><td>Overnite印度快递</td></tr><tr><td>rl-carriers</td><td>RL Carriers</td><td>RL Carriers</td></tr><tr><td>nanjingwoyuan</td><td>Nanjing Woyuan</td><td>南京沃源</td></tr><tr><td>lwehk</td><td>LWE</td><td>LWE</td></tr><tr><td>hhexp</td><td>Hua Han Logistics</td><td>华翰物流</td></tr><tr><td>envialia</td><td>Envialia</td><td>Envialia</td></tr><tr><td>canpar</td><td>Canpar Courier</td><td>Canpar Courier</td></tr><tr><td>17postservice</td><td>17 Post Service</td><td>17 Post Service</td></tr><tr><td>delcart-in</td><td>Delcart</td><td>Delcart</td></tr><tr><td>citylinkexpress</td><td>City-Link Express</td><td>City-Link(信递联）</td></tr><tr><td>2go</td><td>2GO</td><td>2GO</td></tr><tr><td>xend</td><td>Xend Express</td><td>Xend</td></tr><tr><td>air21</td><td>AIR21</td><td>AIR21</td></tr><tr><td>airspeed</td><td>Airspeed International Corporation</td><td>Airspeed International Corporation</td></tr><tr><td>raf</td><td>RAF Philippines</td><td>RAF Philippines</td></tr><tr><td>tiki</td><td>Tiki</td><td>Tiki</td></tr><tr><td>wahana</td><td>Wahana</td><td>Wahana</td></tr><tr><td>ghn</td><td>Giao Hàng Nhanh</td><td>Giao Hàng Nhanh</td></tr><tr><td>viettelpost</td><td>Viettel Post</td><td>Viettel Post</td></tr><tr><td>dotzot</td><td>Dotzot</td><td>Dotzot</td></tr><tr><td>kangaroo-my</td><td>Kangaroo Worldwide Express</td><td>Kangaroo Worldwide Express</td></tr><tr><td>cuckooexpress</td><td>Cuckoo Express</td><td>布谷鸟速递</td></tr><tr><td>maxcellents</td><td>Maxcellents Pte Ltd</td><td>Maxcellents Pte Ltd</td></tr><tr><td>nationwide-my</td><td>Nationwide Express</td><td>Nationwide Express</td></tr><tr><td>rpxonline</td><td>RPX Online</td><td>RPX保时达国际快递</td></tr><tr><td>nhans-solutions</td><td>Nhans Solutions</td><td>Nhans Solutions</td></tr><tr><td>jet-ship</td><td>Jet-Ship Worldwide</td><td>Jet-Ship Worldwide</td></tr><tr><td>ecargo-asia</td><td>Ecargo</td><td>Ecargo</td></tr><tr><td>delhivery</td><td>Delhivery</td><td>Delhivery</td></tr><tr><td>nuvoex</td><td>NuvoEx</td><td>NuvoEx</td></tr><tr><td>parcelled-in</td><td>Parcelled.in</td><td>Parcelled.in</td></tr><tr><td>ecom-express</td><td>Ecom Express</td><td>Ecom Express</td></tr><tr><td>gdex</td><td>GDEX</td><td>GDEX</td></tr><tr><td>skynet</td><td>SkyNet Malaysia</td><td>SkyNet</td></tr><tr><td>sfcservice</td><td>SFC Service</td><td>三态速递</td></tr><tr><td>ec-firstclass</td><td>EC-Firstclass</td><td>EC-Firstclass</td></tr><tr><td>wedo</td><td>WeDo Logistics</td><td>WeDo Logistics</td></tr><tr><td>jcex</td><td>JCEX</td><td>JCEX佳成</td></tr><tr><td>cnexps</td><td>CNE Express</td><td>CNE国际快递</td></tr><tr><td>equick-cn</td><td>Equick China</td><td>EQUICK国际快递</td></tr><tr><td>empsexpress</td><td>EMPS Express</td><td>EMPS Express</td></tr><tr><td>cpacket</td><td>CPacket</td><td>CPacket</td></tr><tr><td>dpe-express</td><td>DPE Express</td><td>递必易</td></tr><tr><td>bondscouriers</td><td>Bonds Couriers</td><td>Bonds Couriers</td></tr><tr><td>courierpost</td><td>CourierPost</td><td>CourierPost</td></tr><tr><td>acommerce</td><td>ACOMMERCE</td><td>ACommerce</td></tr><tr><td>139express</td><td>139 ECONOMIC Package</td><td>139快递</td></tr><tr><td>ubi-logistics</td><td>UBI Logistics</td><td>UBI Logistics</td></tr><tr><td>directfreight-au</td><td>Direct Freight</td><td>Direct Freight快递</td></tr><tr><td>mrw-spain</td><td>MRW</td><td>MRW</td></tr><tr><td>packlink</td><td>Packlink</td><td>Packlink</td></tr><tr><td>colis-prive</td><td>Colis Privé</td><td>Colis Privé</td></tr><tr><td>dmm-network</td><td>DMM Network</td><td>DMM Network</td></tr><tr><td>opek</td><td>FedEx Poland Domestic</td><td>波兰FedEx</td></tr><tr><td>sgt-it</td><td>SGT Corriere Espresso</td><td>SGT Corriere Espresso</td></tr><tr><td>kgmhub</td><td>KGM Hub</td><td>KGM Hub</td></tr><tr><td>qxpress</td><td>Qxpress</td><td>Qxpress</td></tr><tr><td>parcel-express</td><td>Parcel Express</td><td>Parcel Express</td></tr><tr><td>srekorea</td><td>SRE Korea</td><td>SRE Korea</td></tr><tr><td>taqbin-jp</td><td>Yamato Japan</td><td>Yamato宅急便</td></tr><tr><td>sagawa</td><td>Sagawa</td><td>佐川急便Sagawa</td></tr><tr><td>abxexpress-my</td><td>ABX Express</td><td>ABX Express</td></tr><tr><td>mypostonline</td><td>Mypostonline</td><td>Mypostonline</td></tr><tr><td>jam-express</td><td>Jam Express</td><td>Jam Express</td></tr><tr><td>jayonexpress</td><td>Jayon Express (JEX)</td><td>Jayon Express (JEX)</td></tr><tr><td>rpx</td><td>RPX Indonesia</td><td>RPX Indonesia</td></tr><tr><td>raiderex</td><td>RaidereX</td><td>RaidereX</td></tr><tr><td>rzyexpress</td><td>RZY Express</td><td>RZY Express</td></tr><tr><td>airpak-express</td><td>Airpak Express</td><td>Airpak Express</td></tr><tr><td>lbcexpress</td><td>LBC Express</td><td>LBC Express</td></tr><tr><td>pandulogistics</td><td>Pandu Logistics</td><td>Pandu Logistics</td></tr><tr><td>fedex-uk</td><td>FedEx UK</td><td>英国FedEx</td></tr><tr><td>collectplus</td><td>Collect+</td><td>Collect+</td></tr><tr><td>skynetworldwide-uk</td><td>Skynet Worldwide Express UK</td><td>Skynet Worldwide Express UK</td></tr><tr><td>hermes</td><td>Hermesworld</td><td>Hermesworld</td></tr><tr><td>nightline</td><td>Nightline</td><td>Nightline</td></tr><tr><td>apc</td><td>APC Postal Logistics</td><td>APC Postal Logistics</td></tr><tr><td>newgistics</td><td>Newgistics</td><td>Newgistics</td></tr><tr><td>old-dominion</td><td>Old Dominion Freight Line</td><td>Old Dominion Freight Line</td></tr><tr><td>estes</td><td>Estes</td><td>Estes</td></tr><tr><td>greyhound</td><td>Greyhound</td><td>Greyhound</td></tr><tr><td>globegistics</td><td>Globegistics Inc</td><td>Globegistics Inc</td></tr><tr><td>tgx</td><td>TGX</td><td>TGX精英速运</td></tr><tr><td>zjs-express</td><td>ZJS International</td><td>宅急送快递</td></tr><tr><td>hermes-de</td><td>Hermes Germany</td><td>德国Hermes</td></tr><tr><td>international-seur</td><td>International Seur</td><td>International Seur</td></tr><tr><td>trakpak</td><td>TrakPak</td><td>TrakPak</td></tr><tr><td>matkahuolto</td><td>Matkahuolto</td><td>Matkahuolto</td></tr><tr><td>acscourier</td><td>ACS Courier</td><td>ACS Courier</td></tr><tr><td>dpd-poland</td><td>DPD Poland</td><td>波兰DPD</td></tr><tr><td>taxydromiki</td><td>Geniki Taxydromiki</td><td>Geniki Taxydromiki</td></tr><tr><td>adicional</td><td>Adicional Logistics</td><td>Adicional Logistics</td></tr><tr><td>cbl-logistica</td><td>CBL Logistics</td><td>CBL Logistics</td></tr><tr><td>redur-es</td><td>Redur Spain</td><td>Redur Spain</td></tr><tr><td>siodemka</td><td>Siodemka</td><td>Siodemka</td></tr><tr><td>exapaq</td><td>Exapaq</td><td>Exapaq</td></tr><tr><td>cainiao</td><td>Aliexpress Standard Shipping</td><td>速卖通线上物流</td></tr><tr><td>ets-express</td><td>ETS Express</td><td>俄通收</td></tr><tr><td>al8856</td><td>Ali Business Logistics</td><td>阿里电商物流</td></tr><tr><td>anjun</td><td>Anjun Logistics</td><td>安骏物流</td></tr><tr><td>quantium</td><td>Quantium</td><td>冠庭国际物流</td></tr><tr><td>xqwl</td><td>XQ Express</td><td>星前物流</td></tr><tr><td>alpha-fast</td><td>Alpha Fast</td><td>Alpha Fast快递</td></tr><tr><td>omniparcel</td><td>Omni Parcel</td><td>Omni Parcel快递</td></tr><tr><td>cdek</td><td>CDEK Express</td><td>CDEK快递</td></tr><tr><td>trackon</td><td>Trackon Courier</td><td>Trackon</td></tr><tr><td>yunda</td><td>Yunda Express</td><td>韵达快递</td></tr><tr><td>postnl-3s</td><td>PostNL International 3S</td><td>PostNL International 3S</td></tr><tr><td>adsone</td><td>ADSOne</td><td>ADSOne快递</td></tr><tr><td>landmark-global</td><td>Landmark Global</td><td>Landmark Global快递</td></tr><tr><td>thecourierguy</td><td>The Courier Guy</td><td>The Courier Guy</td></tr><tr><td>smsa-express</td><td>SMSA Express</td><td>SMSA快递</td></tr><tr><td>sf-express</td><td>S.F Express</td><td>顺丰速递</td></tr><tr><td>sf-express</td><td>S.F Express</td><td>顺丰速递</td></tr><tr><td>buylogic</td><td>Buylogic</td><td>捷买送</td></tr><tr><td>inpost-paczkomaty</td><td>InPost Paczkomaty</td><td>InPost Paczkomaty</td></tr><tr><td>star-track</td><td>Star Track Express</td><td>Star Track 快递</td></tr><tr><td>qfkd</td><td>QFKD Express</td><td>全峰快递</td></tr><tr><td>jd</td><td>JD Express</td><td>京东快递</td></tr><tr><td>ttkd</td><td>TTKD Express</td><td>天天快递</td></tr><tr><td>deppon</td><td>DEPPON</td><td>德邦物流</td></tr><tr><td>cacesapostal</td><td>Cacesa Postal</td><td>Cacesa南美专线</td></tr><tr><td>chukou1</td><td>Chukou1 Logistics</td><td>出口易</td></tr><tr><td>arrowxl</td><td>Arrow XL</td><td>Arrow XL</td></tr><tr><td>xdp-uk</td><td>XDP Express</td><td>XDP Express</td></tr><tr><td>imexglobalsolutions</td><td>IMEX Global Solutions</td><td>IMEX Global Solutions</td></tr><tr><td>easy-mail</td><td>Easy Mail</td><td>Easy Mail</td></tr><tr><td>idexpress</td><td>IDEX</td><td>IDEX</td></tr><tr><td>rrdonnelley</td><td>RR Donnelley</td><td>RR Donnelley</td></tr><tr><td>con-way</td><td>Con-way Freight</td><td>Con-way Freight</td></tr><tr><td>ninjavan</td><td>Ninja Van</td><td>Ninja Van</td></tr><tr><td>speedexcourier</td><td>Speedex Courier</td><td>Speedex Courier</td></tr><tr><td>expeditors</td><td>Expeditors</td><td>Expeditors</td></tr><tr><td>spsr</td><td>SPSR</td><td>中俄快递SPSR</td></tr><tr><td>chronopost-portugal</td><td>Chronopost Portugal</td><td>Chronopost Portugal</td></tr><tr><td>dwz</td><td>DWZ Express</td><td>递五洲国际物流</td></tr><tr><td>xpressbees</td><td>XpressBees</td><td>XpressBees</td></tr><tr><td>courier-it</td><td>Courier IT</td><td>Courier IT</td></tr><tr><td>specialised-freight</td><td>Specialised Freight</td><td>Specialised Freight</td></tr><tr><td>ups-mi</td><td>UPS Mail Innovations</td><td>UPS Mail Innovations</td></tr><tr><td>dpe-south-africa</td><td>DPE South Africa</td><td>DPE South Africa</td></tr><tr><td>dawn-wing</td><td>Dawn Wing</td><td>Dawn Wing</td></tr><tr><td>fastrak-services</td><td>Fastrak Services</td><td>Fastrak Services</td></tr><tr><td>nova-poshta</td><td>Nova Poshta</td><td>Nova Poshta</td></tr><tr><td>md-express</td><td>MC Express</td><td>茂聪国际物流</td></tr><tr><td>uc-express</td><td>UC Express</td><td>优速快递</td></tr><tr><td>takesend</td><td>Takesend Logistics</td><td>泰嘉物流</td></tr><tr><td>dhl-parcel-nl</td><td>DHL Netherlands</td><td>DHL Netherlands</td></tr><tr><td>roadbull</td><td>Roadbull Logistics</td><td>Roadbull Logistics</td></tr><tr><td>dhl-benelux</td><td>DHL Benelux</td><td>DHL Benelux</td></tr><tr><td>tk-kit</td><td>Tk Kit</td><td>Tk Kit</td></tr><tr><td>abf</td><td>ABF Freight</td><td>ABF Freight</td></tr><tr><td>couriers-please</td><td>Couriers Please</td><td>Couriers Please</td></tr><tr><td>cess</td><td>Cess</td><td>国通快递</td></tr><tr><td>bestex</td><td>Best Express</td><td>百世快递</td></tr><tr><td>gofly</td><td>Gofly</td><td>Gofly</td></tr><tr><td>sinoair</td><td>SINOAIR</td><td>中外运</td></tr><tr><td>italy-sda</td><td>Italy SDA</td><td>意大利SDA</td></tr><tr><td>t-cat</td><td>T Cat</td><td>黑貓宅急便</td></tr><tr><td>fastgo</td><td>Fastgo</td><td>速派快递FastGo</td></tr><tr><td>pca</td><td>PCA</td><td>PCA</td></tr><tr><td>ftd</td><td>FTD Express</td><td>富腾达快递</td></tr><tr><td>shipgce</td><td>Shipgce Express</td><td>飞洋快递</td></tr><tr><td>wise-express</td><td>Wise Express</td><td>万色速递</td></tr><tr><td>cnpex</td><td>Cnpex</td><td>中邮快递</td></tr><tr><td>1hcang</td><td>1hcang</td><td>1号仓</td></tr><tr><td>tea-post</td><td>Tea post</td><td>亚欧快运TEA</td></tr><tr><td>sunyou</td><td>Sunyou</td><td>顺友物流</td></tr><tr><td>dhlecommerce-asia</td><td>DHL Global Mail Asia</td><td>DHL Global Mail Asia</td></tr><tr><td>dhl-active</td><td>DHL Active Tracing</td><td>DHL Active Tracing</td></tr><tr><td>tnt-reference</td><td>TNT Reference</td><td>TNT Reference</td></tr><tr><td>j-net</td><td>J-NET Express</td><td>J-NET捷网</td></tr><tr><td>jiayi56</td><td>Jiayi Express</td><td>佳怡物流</td></tr><tr><td>deltec-courier</td><td>Deltec Courier</td><td>Deltec Courier</td></tr><tr><td>miuson-international</td><td>Miuson Express</td><td>深圳淼信国际物流</td></tr><tr><td>espeedpost</td><td>Espeedpost</td><td>易速国际物流</td></tr><tr><td>asendia-hk</td><td>Asendia HK</td><td>Asendia HK</td></tr><tr><td>gaticn</td><td>GATI Courier</td><td>GATI上海迦递</td></tr><tr><td>szdpex</td><td>DPEX China</td><td>DPEX国际快递（中国）</td></tr><tr><td>tnt-click</td><td>TNT Click</td><td>TNT Click</td></tr><tr><td>rufengda</td><td>Rufengda</td><td>如风达</td></tr><tr><td>mailamericas</td><td>MailAmericas</td><td>MailAmericas</td></tr><tr><td>far800</td><td>Far International Logistics</td><td>泛远国际物流</td></tr><tr><td>winit</td><td>Winit</td><td>winit万邑通</td></tr><tr><td>360zebra</td><td>360zebra</td><td>斑马物联网</td></tr><tr><td>auexpress</td><td>Auexpress</td><td>澳邮中国快运AuExpress</td></tr><tr><td>freakyquick</td><td>freaky quick logistics</td><td>FQ狂派速递</td></tr><tr><td>sure56</td><td>Sure56</td><td>速尔快递</td></tr><tr><td>kye</td><td>KUAYUE EXPRESS</td><td>跨越速运</td></tr><tr><td>kjkd</td><td>Fast Express</td><td>快捷快递</td></tr><tr><td>fetchr</td><td>Fetchr</td><td>Fetchr</td></tr><tr><td>flywayex</td><td>Flyway Express</td><td>程光快递</td></tr><tr><td>china-russia56</td><td>China Russia56</td><td>俄必达A79</td></tr><tr><td>efspost</td><td>EFSPost</td><td>平安快递</td></tr><tr><td>eyoupost</td><td>Eyou800</td><td>易友通</td></tr><tr><td>fd-express</td><td>FD Express</td><td>方递物流</td></tr><tr><td>zes-express</td><td>ESHUN International Logistics</td><td>俄顺国际物流</td></tr><tr><td>utec</td><td>utec</td><td>UTEC瞬移达</td></tr><tr><td>jiaji</td><td>CNEX</td><td>佳吉物流</td></tr><tr><td>xdexpress</td><td>XDEXPRESS</td><td>迅达速递</td></tr><tr><td>xdexpress</td><td>XDEXPRESS</td><td>迅达速递</td></tr><tr><td>13-ten</td><td>13ten</td><td>13ten</td></tr><tr><td>138sd</td><td>138sd</td><td>泰国138快递</td></tr><tr><td>kwt56</td><td>KWT Express</td><td>京华达物流</td></tr><tr><td>wiseloads</td><td>wiseloads</td><td>wiseloads快递</td></tr><tr><td>wndirect</td><td>wndirect</td><td>wndirect快递</td></tr><tr><td>eurodis</td><td>Eurodis</td><td>Eurodis快递</td></tr><tr><td>matdespatch</td><td>Matdespatch</td><td>Matdespatch快递</td></tr><tr><td>tnt-au</td><td>TNT Australia</td><td>澳大利亚TNT</td></tr><tr><td>yakit</td><td>yakit</td><td>yakit快递</td></tr><tr><td>taqbin-hk</td><td>TAQBIN HongKong</td><td>香港宅急便</td></tr><tr><td>ubonex</td><td>UBon &nbsp;Express</td><td>优邦速运</td></tr><tr><td>8dt</td><td>Profit Fields</td><td>永利八达通</td></tr><tr><td>2uex</td><td>2U Express</td><td>优优速递</td></tr><tr><td>ane66</td><td>Ane Express</td><td>安能物流</td></tr><tr><td>aus</td><td>Ausworld Express</td><td>澳世速递</td></tr><tr><td>ewe</td><td>EWE global express</td><td>EWE全球快递</td></tr><tr><td>huidaex</td><td>Huida Express</td><td>美国汇达快递</td></tr><tr><td>allekurier</td><td>allekurier</td><td>AlleKurier</td></tr><tr><td>transrush</td><td>Transrush</td><td>转运四方</td></tr><tr><td>ste56</td><td>Suteng Logistics</td><td>速腾快递</td></tr><tr><td>qexpress</td><td>QEXPRESS</td><td>新西兰易达通</td></tr><tr><td>chinz56</td><td>CHINZ LOGISTICS</td><td>秦远物流</td></tr><tr><td>dpd-hk</td><td>DPD(HK)</td><td>香港DPD</td></tr><tr><td>ztky</td><td>Zhongtie logistic</td><td>中铁物流</td></tr><tr><td>idada56</td><td>Dada logistic</td><td>大达物流</td></tr><tr><td>jersey-post</td><td>Jersey Post</td><td>Jersey Post</td></tr><tr><td>ninjavan-my</td><td>Ninja Van Malaysia</td><td>Ninja Van （马来西亚）</td></tr><tr><td>ninjaxpress</td><td>Ninja Van Indonesia</td><td>Ninja Van （印度尼西亚）</td></tr><tr><td>ninjavan-ph</td><td>Ninja Van Philippines</td><td>Ninja Van (菲律宾)</td></tr><tr><td>ninjavan-th</td><td>Ninja Van Thailand</td><td>Ninja Van （泰国）</td></tr><tr><td>saicheng</td><td>Sai Cheng Logistics</td><td>赛诚国际物流</td></tr><tr><td>8europe</td><td>8europe</td><td>败欧洲</td></tr><tr><td>aplus100</td><td>A PLUS EXPRESS</td><td>美国汉邦快递</td></tr><tr><td>suyd56</td><td>SYD Express</td><td>速邮达物流</td></tr><tr><td>ocschina</td><td>OCS Express</td><td>OCS国际快递</td></tr><tr><td>naqel</td><td>Naqel</td><td>Naqel</td></tr><tr><td>parcel</td><td>Pitney Bowes</td><td>Pitney Bowes</td></tr><tr><td>bsi</td><td>BSI express</td><td>佰事达</td></tr><tr><td>jayeek</td><td>Jayeek</td><td>Jayeek</td></tr><tr><td>blueskyexpress</td><td>Blue Sky Express</td><td>蓝天快递</td></tr><tr><td>blueskyexpress</td><td>Blue Sky Express</td><td>蓝天快递</td></tr><tr><td>safexpress</td><td>Safexpress</td><td>Safexpress</td></tr><tr><td>taqbin-my</td><td>TAQBIN Malaysia</td><td>TAQBIN 马来西亚</td></tr><tr><td>shree-tirupati</td><td>Shree Tirupati Courier</td><td>Shree Tirupati</td></tr><tr><td>imlb2c</td><td>IML Logistics</td><td>IML艾姆勒</td></tr><tr><td>hivewms</td><td>HiveWMS</td><td>海沧无忧</td></tr><tr><td>uskyexpress</td><td>Uskyexpress</td><td>全酋通Usky</td></tr><tr><td>arkexpress</td><td>Ark express</td><td>方舟国际速递</td></tr><tr><td>winlink</td><td>Winlink logistics</td><td>合联国际物流</td></tr><tr><td>xpresspost</td><td>Xpresspost</td><td>xpresspost</td></tr><tr><td>epacket</td><td>ePacket</td><td>e邮宝</td></tr><tr><td>usps-international</td><td>USPS International</td><td>usps-international</td></tr><tr><td>dtdc-plus</td><td>DTDC Plus</td><td>DTDC Plus</td></tr><tr><td style="word-break: break-all;">dhl-hong-kong</td><td>DHL Hong Kong</td><td>香港DHL</td></tr><tr><td>sgtwl</td><td>SGT Express</td><td>深港台物流</td></tr><tr><td>coe</td><td>COE</td><td>COE</td></tr><tr><td>sprintpack</td><td>SprintPack</td><td>SprintPack</td></tr><tr><td>lhtex</td><td>LHT Express</td><td>联昊通</td></tr><tr><td>wanbexpress</td><td>Wanb Express</td><td>万邦速达</td></tr><tr><td>kawa</td><td>Kawa</td><td>嘉华</td></tr><tr><td>kfy</td><td>iquick fish</td><td>快飞鱼</td></tr><tr><td>alljoy</td><td>Alljoy</td><td>Alljoy</td></tr><tr><td>logistics</td><td>WEL</td><td>世航通运WEL</td></tr><tr><td>un-line</td><td>Un-line</td><td>Un-line</td></tr><tr><td>bab-ru</td><td>BAB international</td><td>北北国际</td></tr><tr><td>cnilink</td><td>CNILINK</td><td>CNILINK</td></tr><tr><td>hkdexpress</td><td>HKD</td><td>HKD</td></tr><tr><td>sxexpress</td><td>SX-Express</td><td>三象速递</td></tr><tr><td>jdpplus</td><td>Jdpplus</td><td>美国急递速递</td></tr><tr><td>showl</td><td>Showl</td><td>森鸿物流</td></tr><tr><td>sendle</td><td>Sendle</td><td>Sendle</td></tr><tr><td>bombino-express</td><td>Bombino Express</td><td>Bombino Express</td></tr><tr><td>iepost</td><td>iepost</td><td>iepost</td></tr><tr><td>whistl</td><td>Whistl</td><td>Whistl</td></tr><tr><td>dxdelivery</td><td>DX Delivery</td><td>DX Delivery</td></tr><tr><td>huilogistics</td><td>Hui Logistics</td><td>荟千物流</td></tr><tr><td>superoz</td><td>SuperOZ Logistics</td><td>速配鸥翼</td></tr><tr><td>leopardschina</td><td>Leopards Express</td><td>LWE云豹</td></tr><tr><td>leopardschina</td><td>Leopards Express</td><td>LWE云豹</td></tr><tr><td>cbtsd</td><td>Better Express</td><td>北泰物流</td></tr><tr><td>overseas-logistics</td><td>Overseas Logistics</td><td>Overseas Logistics 印度快递</td></tr><tr><td>linexsolutions</td><td>Linex</td><td>Linex 快递</td></tr><tr><td>asmred</td><td>ASM</td><td>ASM</td></tr><tr><td>airwings-india</td><td>Airwings Courier Express India</td><td>Airwings Courier Express India</td></tr><tr><td>professional-couriers</td><td>The Professional Couriers (TPC)</td><td>The Professional Couriers (TPC)</td></tr><tr><td>mondialrelay</td><td>Mondial Relay</td><td>Mondial Relay</td></tr><tr><td>bt-exp</td><td>LJS</td><td>利佳顺</td></tr><tr><td>asiafly</td><td>AsiaFly</td><td>上海亚翔</td></tr><tr><td>hanjin</td><td>Hanjin Shipping</td><td>韩进物流</td></tr><tr><td>njfeibao</td><td>Flying Leopards Express</td><td>金陵飞豹快递</td></tr><tr><td>firstflightme</td><td>First&nbsp;Flight Couriers</td><td>First Flight Couriers</td></tr><tr><td>jet</td><td>JET</td><td>JET</td></tr><tr><td>360lion</td><td>360lion Express</td><td>纬狮物联网</td></tr><tr><td>hct</td><td>HCT Express</td><td>新竹物流HCT</td></tr><tr><td>ldxpress</td><td>LDXpress</td><td>林道快递</td></tr><tr><td>doortodoor</td><td>CJ Logistics</td><td>韩国CJ物流</td></tr><tr><td>kuajingyihao</td><td>K1 Express</td><td>跨境壹号</td></tr><tr><td>qi-eleven</td><td>7-ELEVEN</td><td>7-ELEVEN</td></tr><tr><td>orangeconnex</td><td>ORANGE CONNEX</td><td>橙联股份</td></tr><tr><td>007ex</td><td>007EX</td><td>俄顺达</td></tr><tr><td>js-exp</td><td>JS EXPRESS</td><td>急速国际</td></tr><tr><td>gaopost</td><td>Gaopost</td><td>高翔物流</td></tr><tr><td>airfex</td><td>Airfex</td><td>亚风快递</td></tr><tr><td>dbschenker</td><td>DB Schenker</td><td>全球国际货运</td></tr><tr><td>ukmail</td><td>UK Mail</td><td>UK Mail</td></tr><tr><td>yht</td><td>Eshipping</td><td>一海通</td></tr><tr><td>myib</td><td>MyIB</td><td>MyIB</td></tr><tr><td>fulfillmen</td><td>Fulfillmen</td><td>Fulfillmen</td></tr><tr><td>tuffnells</td><td>tuffnells</td><td>tuffnells</td></tr><tr><td>turtle-express</td><td>Turtle express</td><td>海龟国际速递</td></tr><tr><td>ceva-logistics</td><td>CEVA Logistics</td><td>CEVA物流</td></tr><tr><td>ubx-uk</td><td>UBX Express</td><td>UBX Express</td></tr><tr><td>shree-mahabali-express</td><td>Shree Mahabali Express</td><td>Shree Mahabali Express</td></tr><tr><td>ydhex</td><td>YDH</td><td>YDH义达物流</td></tr><tr><td>quickway</td><td>Quickway</td><td>瞬程物流</td></tr><tr><td>yunlu</td><td>YL express</td><td>云路物流</td></tr><tr><td>kerryexpress</td><td>Kerry Express</td><td>Kerry Express</td></tr><tr><td>gel-express</td><td>GEL Express</td><td>GEL 快递</td></tr><tr><td>scorejp</td><td>Scorejp</td><td>中国流通王</td></tr><tr><td>ldlog</td><td>LD Logistics</td><td>龙迅国际物流</td></tr><tr><td>xpost</td><td>XPOST</td><td>XPOST</td></tr><tr><td>myaustrianpost</td><td>GmbH</td><td>澳邮欧洲专线平邮</td></tr><tr><td>cosex</td><td>Cosex</td><td>慧合物流</td></tr><tr><td>ht56</td><td>Hong Tai</td><td>鸿泰物流</td></tr><tr><td>elianpost</td><td>E-lian</td><td>易连供应链</td></tr><tr><td>btd56</td><td>Bao Tongda Freight Forwarding</td><td>深圳宝通达</td></tr><tr><td>shreemaruticourier</td><td>Shree Maruti Courier</td><td>Shree Maruti Courier</td></tr><tr><td>bqc</td><td>BQC</td><td>BQC百千诚物流</td></tr><tr><td>hnfywl</td><td>Fang Yuan</td><td>方圆物流</td></tr><tr><td>kerry-tec</td><td>Kerry Tec</td><td>Kerry Tec</td></tr><tr><td>8256ru</td><td>BEL</td><td>BEL北俄国际</td></tr><tr><td>cxc</td><td>CXC</td><td>CXC物流</td></tr><tr><td>kke</td><td>King Kong Express</td><td>京广速递</td></tr><tr><td>800best</td><td>Best Express(logistic)</td><td>百世快运</td></tr><tr><td>sut56</td><td>Suto Logistics</td><td>速通物流</td></tr><tr><td>yji</td><td>YJI</td><td>延锦国际</td></tr><tr><td>speedpak</td><td>SpeedPAK</td><td>SpeedPAK物流</td></tr><tr><td>zajil</td><td>Zajil</td><td>Zajil快递</td></tr><tr><td>lgs</td><td>Lazada (LEX)</td><td>Lazada (LGS) 快递</td></tr><tr><td>dekun</td><td>Dekun</td><td>德坤物流</td></tr><tr><td>yimidida</td><td>YMDD</td><td>壹米滴答</td></tr><tr><td>ecpost</td><td>ECPOST</td><td>ECPOST</td></tr><tr><td>cre</td><td>CRE</td><td>中铁快运</td></tr><tr><td>famiport</td><td>Famiport</td><td>全家快递</td></tr><tr><td>e-can</td><td>Taiwan Pelican Express</td><td>台湾宅配通快递</td></tr><tr><td>meest</td><td>Meest Express</td><td>Meest快递</td></tr><tr><td>boxc</td><td>Boxc Logistics</td><td>Boxc</td></tr><tr><td>ltian</td><td>Ltian</td><td>乐天国际</td></tr><tr><td>buffaloex</td><td>Buffalo</td><td>Buffalo</td></tr><tr><td>sjtsz</td><td>SJTSZ Express</td><td>盛吉泰快递</td></tr><tr><td>com1express</td><td>Come One express</td><td>商壹国际快递</td></tr><tr><td>grandslamexpress</td><td>Grand Slam Express</td><td>Grand Slam Express</td></tr><tr><td>lbexps</td><td>LiBang International Logistics</td><td>立邦国际物流</td></tr><tr><td>etotal</td><td>eTotal</td><td>eTotal快递</td></tr><tr><td>ueq</td><td>UEQ</td><td>UEQ</td></tr><tr><td>hound</td><td>Hound Express</td><td>Hound Express</td></tr><tr><td>eparcel-kr</td><td>eParcel Korea</td><td>eParcel Korea</td></tr><tr><td>sumxpress</td><td>Sum Xpress</td><td>速玛物流</td></tr><tr><td>suning</td><td>SUNING</td><td>苏宁物流</td></tr><tr><td>dpd-de</td><td>DPD Germany</td><td>德国 DPD</td></tr><tr><td>ledii</td><td>Ledii</td><td>乐递供应链</td></tr><tr><td>hxgj56</td><td>Hanxuan international express</td><td>瀚轩国际物流</td></tr><tr><td>kjy</td><td>KJY Logistics</td><td>跨境翼物流</td></tr><tr><td>euasia</td><td>Euasia Express</td><td>EAX欧亚专线</td></tr><tr><td>1dlexpress</td><td>1DL Express</td><td>e递诺快递</td></tr><tr><td>uvan</td><td>UVAN Express</td><td>UVAN宇环通快递</td></tr><tr><td>nippon</td><td>Nippon Express</td><td>Nippon日本通运</td></tr><tr><td>ninjavan-vn</td><td>Ninja Van Vietnam</td><td>Ninja Van（越南）</td></tr><tr><td>guangchi</td><td>GuangChi Express</td><td>光驰国际物流</td></tr></tbody></table>
EOT;
            $crawler = new Crawler();
            $crawler->addHtmlContent($html);
            $crawler->filterXPath('//tr')->each(function (Crawler $node, $i) use (&$logisticsCompanyCodes) {
                if ($i != 0) {
                    $item = [];
                    $node->children()->each(function (Crawler $node, $i) use (&$item) {
                        switch ($i) {
                            case 0:
                                $name = 'code';
                                break;

                            case 1:
                                $name = 'english';
                                break;

                            case 2:
                                $name = 'chinese';
                                break;

                            default:
                                $name = 'other';
                                break;
                        }
                        $item[$name] = trim($node->text());
                    });
                    $item && $logisticsCompanyCodes[] = $item;
                }
            });
        } catch (\Exception $e) {
            $this->stderr($e->getMessage() . PHP_EOL);
        }
        $fnGuessCompanyCode = function ($name, $defaultValue = null) use ($logisticsCompanyCodes) {
            $code = null;
            $name = trim($name);
            $items = $logisticsCompanyCodes;
            foreach ($items as $i => $item) {
                $v = similar_text($name, $item['chinese']);
                if (!$v) {
                    unset($items[$i]);
                } else {
                    $items[$i]['similar_value'] = $v;
                }
            }

            if ($items) {
                ArrayHelper::multisort($items, 'similar_value', SORT_DESC);
                $code = $items[0]['code'];
            }

            return $code ? $code : $defaultValue;
        };

        $dxmAccounts = $db->createCommand('SELECT [[username]], [[cookies]], [[id]] FROM {{%wuliu_dxm_account}} WHERE [[is_valid]] = ' . Constant::BOOLEAN_TRUE . " AND [[cookies]] <> ''")->queryAll();
        foreach ($dxmAccounts as $dxmAccount) {
            $client = new Client([
                'headers' => [
                    'user-agent' => 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/74.0.3729.157 Safari/537.36',
                    'host' => 'www.xxx.com',
                    'Referer' => 'https://www.xxx.com/order/index.htm',
                ],
                'cookies' => CookieJar::fromArray($this->getCookiesArray($dxmAccount['cookies']), 'www.xxx.com'),
            ]);
            $this->stdout("正在获取xxx账号 [" . $dxmAccount['username'] . "] 数据" . PHP_EOL);
            $url = 'https://www.xxx.com/agent/getAgentByType.htm?isEnable=1&type=2'; // 已经授权的

            $response = $client->get($url);
            if ($response->getStatusCode() == '200') {
                $html = $response->getBody()->getContents();
                $crawler = new Crawler();
                $crawler->addHtmlContent($html);

                //  tr 元素
                $trs = $crawler->filterXPath('//tr[contains (@id, "agentTr")]');
                if (!$trs->count()) {
                    $this->stdout("> Error : The cookies of user [" . $dxmAccount['username'] . '] maybe invalid' . PHP_EOL);
                    $errorMessages[] = "> Error : The cookies of user [" . $dxmAccount['username'] . '] maybe invalid' . PHP_EOL;
                    $cmd->update('{{%wuliu_dxm_account}}', ['is_valid' => Constant::BOOLEAN_FALSE], ['id' => $dxmAccount['id']])->execute();
                    Yii::$app->queue->push(new DxmCookieJob([
                        'id' => $dxmAccount['id'],
                    ]));
                    continue;
                }
                foreach ($trs as $tr) {
                    $errorMessages = [];
                    $companyCrawler = new Crawler();
                    $companyCrawler->add($tr);

                    $node = $companyCrawler->filterXPath('node()');
                    $authId = $node->count() ? str_replace('agentTr', '', $node->attr('id')) : '';
                    $agentId = $node->count() ? str_replace('cTr childAgent', '', $node->attr('class')) : '';

                    $companyName = $companyCrawler->filterXPath('node()/preceding-sibling::tr[contains (@class, "agent' . $agentId . '")]//div[@class="agent-name"]/span');
                    $companyName = $companyName->count() ? u($companyName->text())->trim()->toString() : '';

                    $lineNamePrefix = $companyCrawler->filter('td');
                    $lineNamePrefix = $lineNamePrefix->count() ? u($lineNamePrefix->text())->trim()->toString() : null;

                    $mobilePhone = $companyCrawler->filterXPath('node()/preceding-sibling::tr[1]//div[@class="collect-right"]');
                    $mobilePhone = $mobilePhone->count() ? str_replace('电话：', '', $mobilePhone->text()) : null;
                    $mobilePhone && $mobilePhone = u($mobilePhone)->trim()->toString();

                    $this->stdout(u($companyName)->padBoth(80, '=')->toString() . PHP_EOL);
                    $save = true;
                    $companyModel = Company::findOne(['name' => $companyName]);
                    if ($companyModel === null) {
                        $companyModel = new Company();
                        $companyModel->loadDefaultValues();
                        $payload = [
                            'name' => $companyName,
                            'linkman' => $companyName,
                            'mobile_phone' => $mobilePhone ?: '-',
                            'website_url' => 'http://' . $authId . '.com',
                        ];
                        $companyModel->load($payload, '');
                    } elseif ($companyModel->code != $companyName) {
                        // Update
                        $save = false;
                    }
                    if ($save) {
                        $code = $fnGuessCompanyCode($companyName, $companyName);
                        if ($code != $companyName) {
                            $exist = $db->createCommand('SELECT COUNT(*) FROM {{%wuliu_company}} WHERE [[code]] = :code', [
                                ':code' => $code,
                            ])->queryScalar();
                            if ($exist) {
                                $code = $companyName;
                            }
                        }
                        $companyModel->code = $code;
                        if ($companyModel->save()) {
                            $this->stdout(($companyModel->getIsNewRecord() ? 'Insert' : 'Update') . " company successful." . PHP_EOL);
                        } else {
                            $this->stderr("Company Model Error" . PHP_EOL);
                            $this->stderr(var_export($companyModel->toArray(), true) . PHP_EOL);
                            foreach ($companyModel->errors as $filed => $errors) {
                                foreach ($errors as $error) {
                                    $this->stderr(' >> ' . $error . PHP_EOL);
                                }
                            }
                            continue;
                        }
                    }

                    if ($authId) {
                        $companyId = $companyModel->id;
                        $this->stdout(' > Get lines data... ' . PHP_EOL);
                        $response = $client->post('https://www.xxx.com/agent/getAgentProvider.htm', [
                            'form_params' => [
                                'id' => $authId,
                                'type' => -1,
                            ]
                        ]);
                        if ($response->getStatusCode() == '200') {
                            $html = $response->getBody()->getContents();
                            $lineCrawler = new Crawler();
                            $lineCrawler->addHtmlContent($html);

                            $lineCrawler->filterXPath('//tr[@id]')->each(function (Crawler $node, $i) use (&$errorMessages, $companyId, $lineNamePrefix) {
                                /* @var $node Crawler */
                                $lineName = $node->filterXPath('node()/td[1]');
                                $lineName = $lineName->count() ? u($lineName->text())->trim()->toString() : '';
                                $enabled = $node->filterXPath('node()//span[@class="bgColor9 p5"]')->count();

                                if ($enabled) {
                                    $message = '#' . ($i + 1) . " [ $lineName ] ";
                                    if (!CompanyLine::findOne(['name' => $lineName, 'company_id' => $companyId])) {
                                        // Insert Line
                                        $companyLineModel = new CompanyLine();
                                        $companyLineModel->loadDefaultValues();
                                        $payload = [
                                            'name_prefix' => $lineNamePrefix,
                                            'name' => $lineName,
                                            'company_id' => $companyId,
                                        ];
                                        if ($companyLineModel->load($payload, '') && $companyLineModel->save()) {
                                            $this->stdout($message . "insert successful." . PHP_EOL);
                                        } else {
                                            $this->stdout("CompanyLine Model error:" . PHP_EOL);
                                            $this->stderr(var_export($companyLineModel->toArray(), true) . PHP_EOL);
                                            foreach ($companyLineModel->errors as $filed => $errors) {
                                                foreach ($errors as $error) {
                                                    $this->stderr(" >> $error" . PHP_EOL);
                                                }
                                            }
                                        }
                                    } else {
                                        $this->stdout($message . 'already exists, nothing.' . PHP_EOL);
                                    }
                                }
                            });
                            sleep(1);
                        } else {
                            $this->stderr($response->getStatusCode() . ' ' . $response->getBody()->getContents() . PHP_EOL);
                        }
                    }
                }
            } else {
                $this->stdout(' > HTTP CODE ERROR:' . $response->getStatusCode() . PHP_EOL);
            }
        }

        $this->stdout('Done!');
    }

    /**
     * 同步包裹称重数据到xxx并自动发货
     *
     * @throws \yii\db\Exception
     */
    public function actionToDxm()
    {
        $this->stdout("Begin..." . PHP_EOL);
        /* @var $queue Queue */
        $queue = Yii::$app->queue;
        $packageIds = Yii::$app->getDb()->createCommand("SELECT [[id]] FROM {{%wuliu_package}} WHERE [[is_synced]] = :sync", [':sync' => Package::SYNC_PENDING])->queryColumn();
        foreach ($packageIds as $packageId) {
            $this->stdout("Package #{$packageId}..." . PHP_EOL);
            $queue->push(new Package2DxmJob([
                'id' => $packageId,
            ]));
        }
        $this->stdout("Done.");
    }

    /**
     * Dxm 订单同步,默认情况下同步近三天的订单
     *
     * @param null $date
     * @param int $days
     * @throws \Throwable
     * @throws \yii\db\Exception
     */
    public function actionOrders($date = null, $days = 2)
    {
        $days = abs((int) $days);
        $db = Yii::$app->getDb();
        $cmd = $db->createCommand();
        $url = "https://www.xxx.com/package/advancedSearch.htm?pageSize=10&state=approved&isOversea=-1&isVoided=-1&isRemoved=-1&orderId=&packageNum=&buyerAccount=&contactName=&batchNum=&shopId=-1&platform=&contentStr=&searchTypeStr=&authId=-1&country=&shippedStart=&shippedEnd=&storageId=0&productStatus=&priceStart=0.0&priceEnd=0.0&productCountStart=0&timeOut=0&productCountEnd=0&isPrintMd=-1&isPrintJh=-1&isHasOrderComment=-1&isHasOrderMessage=-1&isHasPickComment=-1&commitPlatform=&isGreen=0&isYellow=0&isOrange=0&isRed=0&isViolet=0&isBlue=0&cornflowerBlue=0&pink=0&teal=0&turquoise=0&history=&orderField=order_create_time&isSearch=&isMerge=0&isSplit=0&isReShip=0&isRefund=0&platformSku=&productSku=";
        $dxmAccounts = $db->createCommand("SELECT [[username]], [[cookies]], [[id]] FROM {{%wuliu_dxm_account}} WHERE [[is_valid]] = " . Constant::BOOLEAN_TRUE . " AND [[cookies]] <> ''")->queryAll();
        foreach ($dxmAccounts as $dxmAccount) {
            $flag = false; // dxm cookie 失效标志
            $client = new Client([
                'headers' => [
                    'user-agent' => 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/74.0.3729.157 Safari/537.36',
                    'host' => 'www.xxx.com',
                    'Referer' => 'https://www.xxx.com/order/index.htm',
                ],
                'cookies' => CookieJar::fromArray($this->getCookiesArray($dxmAccount['cookies']), 'www.xxx.com'),

            ]);
            $this->stdout("正在获取xxx账号 [" . $dxmAccount['username'] . "] 数据" . PHP_EOL);
            try {
                if ($date) {
                    $datetime = new DateTime($date);
                } else {
                    $datetime = new DateTime();
                }
            } catch (\Exception $e) {
                $datetime = new DateTime();
            }
            for ($day = 0; $day <= $days; $day++) {
                $pageNo = 1;
                if ($day) {
                    $datetime->modify("-1 days");
                }
                // 付款日期筛选
                $orderPayEnd = $orderPayStart = $datetime->format('Y-m-d');
                $url .= "&orderCreateStart=" . $orderPayStart . "&orderCreateEnd=" . $orderPayEnd;
                $this->stdout("Date " . $orderPayStart . PHP_EOL);
                while (true) {
                    $errorMessages = [];
                    if (isset($totalPage) && $pageNo > $totalPage) {
                        $totalPage = null;
                        break;
                    }
                    $this->stdout("Page #$pageNo" . PHP_EOL);
                    $orderList = [];
                    $detail = [];
                    $response = $client->request('GET', $url . '&pageNo=' . $pageNo);
                    if ($response->getStatusCode() == '200') {
                        $html = $response->getBody()->getContents();
                        $crawler = new Crawler();
                        $crawler->addHtmlContent($html);

                        try {
                            if (!isset($totalPage)) {
                                $totalPage = (int) $crawler->filterXPath('//input[@id="totalPage"]')->attr('value');
                            }
                            $crawler->filterXPath('//*[@id="orderListTable"]/tbody/tr[not(@class)]')->each(function ($node) use (&$orderList) {
                                /* @var $node Crawler */
                                $price = $node->filterXPath('node()/td[2]');
                                if ($price->count()) {
                                    $price = explode(' ', explode(' ', $price->text())[0]);
                                    $currency = trim($price[0]);
                                    $totalPrice = isset($price[1]) ? (float) $price[1] : 0;
                                } else {
                                    $currency = "";
                                    $totalPrice = 0;
                                }
                                $orderId = $node->filterXPath('node()/td[4]/a');
                                $orderId = $orderId->count() ? $orderId->text() : '';

                                $td = $node->filterXPath('node()/td[5]');
                                if ($td->count()) {
                                    $time = explode('<br>', $td->html());
                                    foreach ($time as $t) {
                                        if (strpos($t, '付款') !== false) {
                                            $paidAt = explode('：', $t);
                                            $paidAt = isset($paidAt[1]) ? $paidAt[1] : '';
                                            break;
                                        } elseif (strpos($t, '下单') !== false) {
                                            $createdAt = explode('：', $t);
                                            $createdAt = isset($paidAt[1]) ? $createdAt[1] : '';
                                        } else {
                                            continue;
                                        }
                                    }
                                }

                                $packageId = $node->filterXPath('node()/td[2]/p');
                                $packageId = $packageId->count() ? trim($node->filterXPath('node()/td[2]/p')->attr('id'), 'p_') : 0;
                                $productInformation = [];
                                $node->filterXPath('node()/td[1]/table//tr')->each(function ($productNode) use (&$productInformation) {
                                    /* @var $productNode Crawler */

                                    // 判断是产品还是附加的定制信息 tr 元素
                                    if ($productNode->filterXPath('node()//img[@data-order]')->count()) {
                                        $img = $productNode->filterXPath('node()//img[@data-order]');
                                        $img = $img->count() ? $img->attr("data-order") : '';

                                        $sku = $productNode->filterXPath('node()/td[1]//input[@displaysku]');
                                        $sku = $sku->count() ? $sku->attr('displaysku') : '';

                                        $title = $productNode->filterXPath('node()/td[2]/div/p/a');
                                        $title = $title->count() ? $title->text() : '';

                                        $number = $productNode->filterXPath('node()/td[2]/div/p/span');
                                        $number = $number->count() ? $number->text() : 0;

                                        if ($productNode->filterXPath('node()/td[2]/div/p[2]')->count()) {
                                            $price = trim(explode(' ', $productNode->filterXPath('node()/td[2]/div/p[2]')->text())[1]);
                                            $currency = trim(explode(' ', $productNode->filterXPath('node()/td[2]/div/p[2]')->text())[0]);
                                        } else {
                                            $price = 0;
                                            $currency = '';
                                        }
                                        $json = [
                                            'names' => [],
                                            'color' => '',
                                            'material' => '',
                                            'size' => '',
                                            'giftBox' => false,
                                            'beads' => 0,
                                            'other' => [],
                                            'raw' => '',
                                        ];
                                        $raw = [];
                                        $names = [];
                                        // p标签
                                        $p = $productNode->filterXPath('node()/td[2]/div/p');
                                        foreach ($p as $element) {
                                            /* @var $element DOMElement */
                                            $ex = explode('：', $element->textContent);
                                            if (count($ex) == 2) {
                                                if (strpos($ex[0], '_') === false) {
                                                    $raw[trim($ex[0])] = trim($ex[1]);
                                                    $json['raw'] = $raw;
                                                    // 解析names/color/size/material/giftBox
                                                    if (stripos($ex[0], 'number') !== false) {
                                                        preg_match('/\d+/', $ex[1], $matches);
                                                        if ($matches) {
                                                            $json['beads'] = $matches[0];
                                                        }
                                                    } elseif (stripos($ex[0], 'please write') !== false || stripos($ex[0], 'name') !== false || stripos($ex[0], 'charm') !== false) {
                                                        foreach (explode(',', $ex[1]) as $name) {
                                                            $names[] = trim($name);
                                                        }
                                                        $json['names'] = $names;
                                                    } elseif (stripos($ex[0], 'gift') !== false) {
                                                        $json['giftBox'] = true;
                                                    } elseif (stripos($ex[0], 'variant') !== false) {
                                                        $variants = explode('/', $ex[1]);
                                                        foreach ($variants as $variant) {
                                                            if (stripos($variant, 'beads') !== false) {
                                                                preg_match('/\d+/', $variant, $matches);
                                                                if ($matches) {
                                                                    $json['beads'] = $matches[0];
                                                                }
                                                            } elseif (stripos($variant, 'sterling silver') !== false || stripos($variant, 'silver plat') !== false) {
                                                                $json['material'] = trim($variant);
                                                            } elseif (stripos($variant, 'size') !== false || stripos($variant, 'cm') !== false || stripos($variant, 'inch') !== false) {
                                                                $json['size'] = trim($variant);
                                                            } elseif (stripos($variant, 'silver') !== false || stripos($variant, 'gold') !== false || stripos($variant, 'black') !== false) {
                                                                $json['color'] = trim($variant);
                                                            } else {
                                                                if (isset($json['other']['variants'])) {
                                                                    $json['other']['variants'] .= ' / ' . trim($variant);
                                                                } else {
                                                                    $json['other']['variants'] = trim($variant);
                                                                }
                                                            }
                                                        }
                                                    } else {
                                                        $json['other'][trim($ex[0])] = trim($ex[1]);
                                                    }
                                                }
                                            }
                                        }
                                        if (!$json['other']) {
                                            $json['other'] = new stdClass();
                                        }

                                        $productInformation[] = [
                                            'title' => $title,
                                            'sku' => $sku,
                                            'img' => $img,
                                            'number' => (int) $number,
                                            'options' => $json,
                                            'itemPrice' => [
                                                'amount' => (float) $price,
                                                'currencyCode' => $currency,
                                            ],
                                        ];
                                    }
                                });
                                $orderList[] = [
                                    'orderTotal' => [
                                        'Amount' => $totalPrice,
                                        'CurrencyCode' => $currency,
                                    ],
                                    'orderId' => $orderId,
                                    'paidDate' => isset($paidAt) ? (new DateTime($paidAt))->getTimestamp() : null,
                                    'purchaseDate' => isset($createdAt) ? (new DateTime($createdAt))->getTimestamp() : null,
                                    'packageId' => $packageId,
                                    'detail' => $productInformation,
                                    'orderStatus' => 1, // 订单默认状态
                                ];
                            });
                            //  对每一个packageId请求详情页面
                            $packageIds = $crawler->filterXPath('//input[@id="orderIdsStr"]')->attr('value');
                            $packageIds = explode(';', $packageIds);
                            $promises = [];
                            foreach ($packageIds as $id) {
                                $this->stdout(' > Get' . $id . " package detail." . PHP_EOL);
                                $promises[$id] = $client->postAsync('https://www.xxx.com/package/detail.htm', [
                                    'form_params' => [
                                        'packageId' => $id,
                                    ],
                                ]);
                            }
                            $results = $promises ? \GuzzleHttp\Promise\unwrap($promises) : [];
                            foreach ($results as $key => $result) {
                                /* @var $result ResponseInterface */
                                if ($result->getStatusCode() == '200') {
                                    $detailCrawler = new Crawler();
                                    $detailCrawler->addHtmlContent($result->getBody()->getContents());

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

                                    $apartmentNumber = $detailCrawler->filterXPath('//input[@id="apartmentNumber"]');
                                    $apartmentNumber = $apartmentNumber->count() ? $apartmentNumber->attr('value') : '';

                                    $address1 = $detailCrawler->filterXPath('//input[@id="detailAddress1"]');
                                    $address1 = $address1->count() ? $address1->attr('value') : '';

                                    $address2 = $detailCrawler->filterXPath('//input[@id="detailAddress2"]');
                                    $address2 = $address2->count() ? $address2->attr('value') : '';

                                    $taxNumber = $detailCrawler->filterXPath('//input[@id="taxNumber"]');
                                    $taxNumber = $taxNumber->count() ? $taxNumber->attr('value') : '';

                                    $detailAddressCountry1 = $detailCrawler->filterXPath('//input[@id="detailAddressCountry1"]');
                                    $detailAddressCountry1 = $detailAddressCountry1->count() ? $detailAddressCountry1->attr('value') : '';

                                    $companyName = $detailCrawler->filterXPath('//input[@id="companyName"]');
                                    $companyName = $companyName->count() ? $companyName->attr('value') : '';

                                    $shopName = $detailCrawler->filterXPath('//div[@class="f-w600"]');
                                    $shopName = $shopName->count() ? str_replace('卖家：', '', trim($shopName->text())) : '';

                                    $platform = $detailCrawler->filterXPath('//input[@id="detailPlatform"]');
                                    $platform = $platform->count() ? $platform->attr('value') : '';

                                    $packageNumber = $detailCrawler->filterXPath('//span[@id="dxmPackageNumDetailSpan"]');
                                    $packageNumber = $packageNumber->count() ? trim($packageNumber->text()) : 0;

                                    $detail[$key] = [
                                        'shopName' => $shopName,
                                        'city' => $city,
                                        'postCode' => $postCode,
                                        'stateOrRegion' => $province,
                                        'countryCode' => $detailAddressCountry1,
                                        'consigneePhone' => $phone,
                                        'consigneeMobilePhone' => $mobilePhone,
                                        'apartmentNumber' => $apartmentNumber,
                                        'consigneeName' => $consigneeName,
                                        'address1' => $address1,
                                        'address2' => $address2,
                                        'companyName' => $companyName,
                                        'taxNumber' => $taxNumber,
                                        'status' => 0,
                                        'packageNumber' => $packageNumber,
                                    ];
                                } else {
                                    $this->stdout(' > HTTP CODE ERROR:' . $response->getStatusCode());
                                }
                            }
                        } catch (InvalidArgumentException $e) {
                            $this->stdout("> Error:" . $e->getMessage() . "Please update the Cookie!" . PHP_EOL);
                            $errorMessages[] = "> Error : The cookies of user [" . $dxmAccount['username'] . '] maybe invalid' . PHP_EOL;
                            $cmd->update('{{%wuliu_dxm_account}}', ['is_valid' => Constant::BOOLEAN_FALSE], ['id' => $dxmAccount['id']])->execute();
                            break;
                        } catch (\Exception $e) {
                            $errorMessages[] = $e->getFile() . ": Line " . $e->getLine() . " : " . $e->getMessage();
                            $this->stdout(" > Error: " . $e->getMessage() . PHP_EOL);
                        }
                    } else {
                        $errorMessages[] = "Get page $pageNo return error: " . $response->getStatusCode();
                        $this->stdout(' > HTTP CODE ERROR:' . $response->getStatusCode() . PHP_EOL);
                    }
                    //  数据入库
                    foreach ($orderList as $order) {
                        $this->stdout('订单' . $order['orderId'] . "准备入库" . PHP_EOL);
                        //  合并包裹内数据
                        if (isset($detail[$order['packageId']])) {
                            $order = array_merge($order, $detail[$order['packageId']]);
                        } else {
                            $errorMessages[] = "Package {$order['packageId']} not detail.";
                            $this->stdout('不存在包裹' . $order['packageId'] . '的详情' . PHP_EOL);
                            continue;
                        }

                        $transaction = $db->beginTransaction();
                        try {
                            if ($model = Order::findOne(['number' => $order['orderId']])) {
                                // @todo Update order and orderItem
                                $this->stdout("Order [" . $model->number . "] exists." . PHP_EOL);
                            } else {
                                $model = new Order();
                                $model->loadDefaultValues();
                                $shopId = $db->createCommand("SELECT [[id]] FROM {{%g_shop}} WHERE [[name]] = :name", [':name' => $order['shopName']])->queryScalar();
                                $orderLoad = [
                                    'package_id' => $order['packageId'],
                                    'shop_id' => $shopId ?: 0,
                                    'package_number' => $order['packageNumber'],
                                    'number' => $order['orderId'],
                                    'consignee_name' => $order['consigneeName'],
                                    'consignee_mobile_phone' => $order['consigneeMobilePhone'],
                                    'country' => $order['countryCode'],
                                    'consignee_state' => $order['stateOrRegion'],
                                    'consignee_city' => $order['city'],
                                    'consignee_tel' => $order['consigneePhone'],
                                    'consignee_address1' => $order['address1'],
                                    'consignee_address2' => $order['address2'],
                                    'consignee_postcode' => $order['postCode'],
                                    'total_amount' => $order['orderTotal']['Amount'],
                                    'place_order_at' => $order['purchaseDate'],
                                    'payment_at' => $order['paidDate'],
                                    'waybill_number' => '',
                                    'created_at' => time(),
                                    'created_by' => 1,
                                    'updated_at' => time(),
                                    'updated_by' => 1,
                                ];
                                if ($model->load($orderLoad, '') && $model->save()) {
                                    $this->stdout($order['orderId'] . "入库 g_order 表成功" . PHP_EOL);
                                } else {
                                    $this->stdout($order['orderId'] . "入库 g_order 表失败" . PHP_EOL);
                                    foreach ($model->errors as $filed => $errors) {
                                        foreach ($errors as $error) {
                                            $errorMessages[] = $error;
                                        }
                                    }
                                    $transaction->rollBack();
                                    continue;
                                }
                                $error = false;
                                foreach ($order['detail'] as $product) {
                                    $itemModel = new OrderItem();
                                    $itemModel->loadDefaultValues();
                                    $orderItemLoad = [
                                        'order_id' => $model->id,
                                        'sku' => $product['sku'],
                                        'product_name' => $product['title'],
                                        'extend' => $product['options'],
                                        'quantity' => $product['number'],
                                        'sale_price' => $product['itemPrice']['amount'],
                                    ];
                                    if ($product['img']) {
                                        $now = time();
                                        $imgPath = '/uploads/' . date('Y', $now) . '/' . date('m', $now) . '/' . date('d', $now);
                                        $imgName = time() . rand(1000, 9999) . '.jpg';
                                        $path = FileHelper::normalizePath(Yii::getAlias('@webroot') . $imgPath);
                                        if (!file_exists($path)) {
                                            FileHelper::createDirectory($path);
                                        }
                                        $response = $client->request('get', $product['img'], ['save_to' => $path . DIRECTORY_SEPARATOR . $imgName]);
                                        if ($response->getStatusCode() != 200) {
                                            $orderItemLoad['image'] = '';
                                        } else {
                                            $orderItemLoad['image'] = "$imgPath/$imgName";
                                        }
                                    }
                                    if ($itemModel->load($orderItemLoad, '') && $itemModel->save()) {
                                        $this->stdout($order['orderId'] . " 入库 g_order_item 表成功" . PHP_EOL);
                                    } else {
                                        $error = true;
                                        $this->stdout($order['orderId'] . " 入库 g_order_item 表失败" . PHP_EOL);
                                        foreach ($itemModel->errors as $filed => $errors) {
                                            foreach ($errors as $error) {
                                                $errorMessages[] = $error;
                                            }
                                        }
                                        $transaction->rollBack();
                                        break;
                                    }
                                    $omItemModel = new OrderItemBusiness();
                                    $omItemModel->loadDefaultValues();
                                    if ($omItemModel->load([
                                            'order_item_id' => $itemModel->id,
                                        ], '') && $omItemModel->save()) {
                                        $this->stdout($order['orderId'] . "入库om_order_item_business表成功" . PHP_EOL);
                                    } else {
                                        $error = true;
                                        $this->stdout($order['orderId'] . "入库om_order_item_business表失败" . PHP_EOL);
                                        foreach ($omItemModel->errors as $filed => $errors) {
                                            foreach ($errors as $error) {
                                                $errorMessages[] = $error;
                                            }
                                        }
                                        $transaction->rollBack();
                                        break;
                                    }
                                }
                                if ($error) {
                                    continue;
                                }
                            }

                            $transaction->commit();
                        } catch (\Exception $e) {
                            $errorMessages[] = $e->getFile() . ": Line " . $e->getLine() . " : " . $e->getMessage();
                            $this->stdout($e->getMessage() . PHP_EOL);
                            $transaction->rollBack();
                        } catch (\Throwable $e) {
                            $errorMessages[] = $e->getFile() . ": Line " . $e->getLine() . " : " . $e->getMessage();
                            $transaction->rollBack();
                        }
                    }
                    if ($errorMessages) {
                        $this->stdout(str_repeat('=', 80));
                        $this->stdout("Found follow errors: " . PHP_EOL);
                        foreach ($errorMessages as $message) {
                            $this->stdout(" > $message" . PHP_EOL);
                        }
                    }
                    $pageNo += 1;
                    sleep(1);
                }
            }
        }

        $this->stdout("Done." . PHP_EOL);
    }

}
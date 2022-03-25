<?php

namespace app\extensions\logisticsService;

use Exception;
use GuzzleHttp\Client;
use function Symfony\Component\String\u;

/**
 * 燕文物流接口对接
 *
 * @package app\extensions\logisticsService
 */
class YanWenLogisticsService extends LogisticsServiceAbstract
{

    private function getHttpHeader()
    {
        return [
            'Authorization' => 'basic ' . $this->getConfigValue('identity.token'),
            'Content-Type' => 'text/xml; charset=utf-8',
            'Accept' => 'application/xml',
        ];
    }

    public function getEndpoint(): string
    {
        return str_replace('{userId}', $this->getConfigValue('identity.userId', '{userId}'), parent::getEndpoint());
    }

    /**
     * 获取物流发货渠道
     *
     * @return array
     */
    public function getChannels(): array
    {
        $channels = [];
        $endpoint = $this->getUrl('/GetChannels');
        $client = new Client();
        $response = $client->get($endpoint, [
            'headers' => $this->getHttpHeader()
        ]);
        if ($response->getStatusCode() == 200) {
            $array = $this->xml2array($response->getBody()->getContents());
            if (isset($array['CallSuccess']) && $array['CallSuccess'] === 'true') {
                $items = $array['ChannelCollection']['ChannelType'] ?? [];
                foreach ($items as $item) {
                    $channel = new Channel();
                    $channel->setId($item['Id']);
                    $channel->setChineseName($item['Name']);
                    $channel->setStatus($item['Status']);
                    $channels[] = $channel;
                }
            }
        }

        return $channels;
    }

    /**
     * 获取物流发货国家
     *
     * @inheritDoc
     */
    public function getCountries(): array
    {
        $countries = [];
        $client = new Client();
        $order = $this->order;
        $channel = $order->getChannel();
        $response = $client->get($this->getUrl("/channels/{$channel->getId()}/countries"), [
            'headers' => $this->getHttpHeader(),
        ]);
        if ($response->getStatusCode() == 200) {
            $array = $this->xml2array($response->getBody()->getContents());
            if (isset($array['CallSuccess']) && $array['CallSuccess'] === 'true') {
                $items = $array['CountryCollection']['CountryType'] ?? [];
                foreach ($items as $item) {
                    $country = new Country();
                    $country->setId($item['Id']);
                    $country->setCode($item['RegionCode'] ?? null);
                    $country->setChineseName($item['RegionCh'] ?? null);
                    $country->setEnglishName($item['RegionEn'] ?? null);
                    $countries[] = $country;
                }
            }
        }

        return $countries;
    }

    public function parsePayload(Order $payload): void
    {
        // TODO: Implement parsePayload() method.
    }

    /**
     * @inheritDoc
     * @throws Exception
     */
    public function createOrder(bool $prediction = false, bool $runValidation = true): Response
    {
        $resp = parent::createOrder($prediction, $runValidation);
        if (!$resp->isSuccess()) {
            return $resp;
        }
        $order = $this->order;
        $items = $order->getItems();
        $n = count($items);
        if ($n == 0) {
            $resp->setErrorMessage("请设置订单详情数据。");

            return $resp;
        }
        $userId = $this->getConfigValue('identity.userId');
        $goods = "";
        foreach ($order->getItems() as $item) {
            /* @var $item OrderItem */
            $goods .= <<<EOT
<GoodsName>
    <Userid>{$userId}</Userid>
    <NameCh>{$item->getChineseName()}</NameCh>
    <NameEn>{$item->getEnglishName()}</NameEn>
    <Weight>{$item->getWeight()}</Weight>
    <DeclaredValue>{$item->getPrice()}</DeclaredValue>
    <DeclaredCurrency>{$order->getCurrency()}</DeclaredCurrency>
</GoodsName>
EOT;
        }

        $xml = <<<EOT
<ExpressType>
    <Epcode>{$order->getNumber()}</Epcode>
    <Userid>{$userId}</Userid>
    <Channel>{$order->getChannel()->getId()}</Channel>
    <UserOrderNumber>{$order->getNumber()}</UserOrderNumber>
    <SendDate></SendDate>
    <MRP></MRP>
    <ExpiryDate></ExpiryDate>
    <Receiver>
        <Userid>{$userId}</Userid>
        <Name>{$order->getReceiverName()}</Name>
        <Phone>{$order->getReceiverTel()}</Phone>
        <Mobile>{$order->getReceiverMobilePhone()}</Mobile>
        <Email>{$order->getReceiverEmail()}</Email>
        <Company></Company>
        <Country>{$order->getReceiverCountry()->getEnglishName()}</Country>
        <Postcode>{$order->getReceiverPostcode()}</Postcode>
        <State>{$order->getReceiverState()}</State>
        <City>{$order->getReceiverCity()}</City>
        <Address1>{$order->getReceiverAddress1()}</Address1>
        <Address2>{$order->getReceiverAddress2()}</Address2>
    </Receiver>
    <Sender>
        <TaxNumber></TaxNumber>
    </Sender>
    <Memo>{$order->getRemark()}</Memo>
    <Quantity>{$order->getQuantity()}</Quantity>
    {$goods}
</ExpressType>
EOT;

        $client = new Client();
        $response = $client->post($this->getUrl("/Expresses"), [
            'body' => $xml,
            'headers' => $this->getHttpHeader(),
        ]);
        if ($response->getStatusCode() == 200) {
            $array = $this->xm($response->getBody()->getContents());
            if (isset($array['CallSuccess']) && $array['CallSuccess'] === "true") {
                $resp->setSuccess(true);
                $waybillNumber = $array['CreatedExpress']['YanwenNumber'] ?? null;
                $resp->setData([
                    'waybillNumber' => $waybillNumber,
                    'order' => $this->order,
                ]);
                if ($prediction) {
                    $goodNames = '';
                    foreach ($order->getItems() as $item) {
                        /* @var $item OrderItem */
                        $goodNames .= <<<EOT
<OnlineCustomDataType>              
    <NameCh>{$item->getChineseName()}</NameCh>
    <NameEn>{$item->getEnglishName()}</NameEn>
    <DeclaredValue>{$item->getPrice()}</DeclaredValue>
    <DeclaredCurrency>{$order->getCurrency()}</DeclaredCurrency>
</OnlineCustomDataType>
EOT;
                    }
                    $xml = <<<EOT
<OnlineDataType>
    <Epcode>{$waybillNumber}</Epcode>
    <Userid>{$userId}</Userid>
    <ChannelType>WISH邮-平邮-北京仓</ChannelType>
    <Country>{$order->getReceiverCountry()}</Country>
    <Postcode>{$order->getReceiverPostcode()}</Postcode>
    <SendDate>2016-06-16T00:00:00</SendDate>
    <GoodNames>
        {$goodNames}
    </GoodNames>
</OnlineDataType>
EOT;

                    try {
                        $response = $client->post($this->getUrl('/OnlineData'), [
                            'headers' => $this->getHttpHeader(),
                            'body' => $xml,
                        ]);
                        if ($response->getStatusCode() == 200) {
                            $array = $this->xml2array($response->getBody()->getContents());
                            if (isset($array['Response']['Success']) && $array['Response']['Success'] === "false") {
                                $resp->setSuccess(false);
                                $resp->setErrorMessage($array['Response']['ReasonMessage']);
                            }
                        }
                    } catch (Exception $e) {
                        $resp->setSuccess(false);
                        $resp->setErrorMessage($e->getMessage());
                    }
                }
            } else {
                $resp->setErrorMessage($array['Response']['ReasonMessage'] ?? '未知错误。');
            }
        } else {
            $resp->setErrorMessage("HTTP CODE: " . $response->getStatusCode());
        }

        return $resp;
    }
}

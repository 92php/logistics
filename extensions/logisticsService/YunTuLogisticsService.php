<?php

namespace app\extensions\logisticsService;

use GuzzleHttp\Client;

/**
 * 云途物流接口对接
 *
 * @package app\extensions\logisticsService
 */
class YunTuLogisticsService extends LogisticsServiceAbstract
{

    private function getHttpHeader()
    {
        return [
            'Authorization' => 'Basic ' . base64_encode($this->getConfigValue('identity.userId') . '&' . $this->getConfigValue('identity.apiSecret')),
            'Accept' => 'application/json',
            'charset' => 'UTF-8'
        ];
    }

    /**
     * 获取物流发货渠道
     *
     * @inheritDoc
     */
    public function getChannels(): array
    {
        $channels = [];
        $countryCode = $this->order->getReceiverCountry()->getCode();
        $endpoint = $this->getUrl('/Common/GetShippingMethods?CountryCode=' . $countryCode);
        $client = new Client();
        $response = $client->get($endpoint, [
            'headers' => $this->getHttpHeader()
        ]);
        if ($response->getStatusCode() == 200) {
            $array = json_decode($response->getBody()->getContents(), true);

            if (isset($array['Items']) && $array['Items']) {
                $items = $array['Items'] ?? [];
                foreach ($items as $item) {
                    $channel = new Channel();
                    $channel->setCode($item['Code']);
                    $channel->setChineseName($item['CName']);
                    $channel->setEnglishName($item['EName']);
                    $channels[] = $channel;
                }
            }
        }

        return $channels;
    }

    /**
     * @inheritDoc
     */
    public function getCountries(): array
    {
        $countries = [];
        $client = new Client();
        $response = $client->get($this->getUrl("/Common/GetCountry"), [
            'headers' => $this->getHttpHeader(),
        ]);
        if ($response->getStatusCode() == 200) {
            $array = json_decode($response->getBody()->getContents(), true);
            if (isset($array['Code']) && $array['Code'] === '0000') {
                $items = $array['Items'] ?? [];
                foreach ($items as $item) {
                    $country = new Country();
                    if ($item['CountryCode'] == 'US') {
                        $country->setCode($item['CountryCode'] ?? null);
                        $country->setChineseName($item['CName'] ?? null);
                        $country->setEnglishName($item['EName'] ?? null);
                        $countries[] = $country;
                        break;
                    }
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
     */
    public function createOrder(bool $prediction = false, bool $runValidation = true): Response
    {
        $resp = new Response();
        $order = $this->order;
        $items = $order->getItems();
        $n = count($items);
        if ($n == 0) {
            $resp->setErrorMessage("请设置订单详情数据。");

            return $resp;
        }

        $goods = [];
        foreach ($order->getItems() as $item) {
            /* @var $item OrderItem */
            $goods[] = [
                'EName' => $item->getEnglishName(),
                'CName' => $item->getChineseName(),
                'Quantity' => $item->getQuantity(),
                'UnitWeight' => $item->getWeight(),
                'UnitPrice' => $item->getPrice(),
                'CurrencyCode' => $order->getCurrency()
            ];
        }

        $request = [];
        $request[0]['CustomerOrderNumber'] = $order->getNumber();
        $request[0]['TrackingNumber'] = $order->getTrackingNumber();;
        $request[0]['ShippingMethodCode'] = $order->getChannel()->getCode();
        $request[0]['PackageCount'] = $order->getQuantity();
        $request[0]['weight'] = $order->getWeight();
        $request[0]['Receiver']['CountryCode'] = $order->getReceiverCountry()->getCode();
        $request[0]['Receiver']['FirstName'] = $order->getReceiverName();
        $request[0]['Receiver']['Street'] = $order->getReceiverAddress1();
        $request[0]['Receiver']['City'] = $order->getReceiverCity();
        $request[0]['Receiver']['State'] = $order->getReceiverState();
        $request[0]['Receiver']['Zip'] = $order->getReceiverPostcode();
        $request[0]['Receiver']['Phone'] = $order->getReceiverMobilePhone();
        $request[0]['Parcels'] = $goods;
        $client = new Client();
        $response = $client->post($this->getUrl("/WayBill/CreateOrder"), [
            'json' => $request,
            'headers' => $this->getHttpHeader(),
        ]);
        if ($response->getStatusCode() == 200) {
            $array = json_decode($response->getBody()->getContents(), true);
            if (isset($array['Code']) && $array['Code'] == "0000") {
                $resp->setSuccess(true);
                $WayBillNumbers = [];
                foreach ($array['Item'] as $item) {
                    $WayBillNumbers[] = $item['WayBillNumber'];
                }
                $resp->setData([
                    'waybillNumber' => $WayBillNumbers ?? null,
                ]);
            } else {
                $resp->setErrorMessage($array['Item'][0]['Remark'] ?? '未知错误。');
            }
        } else {
            $resp->setErrorMessage("HTTP CODE: " . $response->getStatusCode());
        }

        return $resp;
    }
}

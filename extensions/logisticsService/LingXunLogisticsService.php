<?php

namespace app\extensions\logisticsService;

use function Symfony\Component\String\u;

/**
 * 领讯物流接口对接
 *
 * @package app\extensions\logisticsService
 */
class LingXunLogisticsService extends LogisticsServiceAbstract
{

    /**
     * 获取物流发货渠道
     *
     * @inheritDoc
     */
    public function getChannels(): array
    {
        $channels = [];
        $client = new \SoapClient ($this->getEndpoint());

        $response = $client->getTransportWayList($this->getConfigValue('identity.token'));
        if ($response->success) {
            if (isset($response->transportWays) && $response->transportWays) {
                $items = $response->transportWays ?? [];
                foreach ($items as $item) {
                    if ($item->used == 'Y') {
                        $channel = new Channel();
                        $channel->setCode($item->code);
                        $channel->setChineseName($item->name);
                        $channels[] = $channel;
                    }
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
        $country = new Country();
        $country->setCode("US");
        $country->setEnglishName("America");
        $country->setChineseName("美国");
        $countries[] = $country;

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
                'name' => $item->getEnglishName(),
                'pieces' => $item->getQuantity() . 'L',
                'netWeight' => $item->getWeight(),
                'unitPrice' => $item->getPrice()
            ];
        }

        $request = [];
        $request['orderNo'] = $order->getNumber();
        $request['transportWayCode'] = 'XXSZEUB';
        $request['cargoCode'] = 'W'; //
        $request['destinationCountryCode'] = $order->getReceiverCountry()->getCode();
        $request['pieces'] = $order->getQuantity() . 'L';
        $request['consigneeName'] = $order->getReceiverName();
        $request['street'] = $order->getReceiverAddress1();
        $request['city'] = $order->getReceiverCity();
        $request['province'] = $order->getReceiverState();
        $request['consigneePostcode'] = $order->getReceiverPostcode();
        $request['consigneeTelephone'] = $order->getReceiverMobilePhone();
        $request['weight'] = $order->getWeight();
        $request['insured'] = 'N';
        $request['goodsCategory'] = 'G';
        $request['declareItems'] = $goods;

        $client = new \SoapClient ($this->getEndpoint());
        $response = $client->createAndAuditOrder($this->getConfigValue('identity.token'), $request);
        if ($response->success) {
            $resp->setSuccess(true);
            $resp->setData([
                'waybillNumber' => $response->trackingNo ?? null,
            ]);
        } else {
            $resp->setErrorMessage($response->error->errorInfo ?? '未知错误。');
        }

        return $resp;
    }
}

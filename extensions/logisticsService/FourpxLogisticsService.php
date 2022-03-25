<?php

namespace app\extensions\logisticsService;

use GuzzleHttp\Client;

/**
 * 递四方物流接口对接
 *
 * @package app\extensions\logisticsService
 */
class FourpxLogisticsService extends LogisticsServiceAbstract
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
    function getChannels(): array
    {
        $channels = [];
        $endpoint = $this->getUrl('/GetChannels');
        $client = new Client();
        $response = $client->get($endpoint, [
            'headers' => $this->getHttpHeader()
        ]);
        if ($response->getStatusCode() == 200) {
            $xml = simplexml_load_string($response->getBody()->getContents());
            $json = json_encode($xml);
            $array = json_decode($json, true);
            if (isset($array['CallSuccess']) && $array['CallSuccess']) {
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
    function getCountries(): array
    {
        return [];
    }

    function parsePayload(Order $payload): void
    {
        // TODO: Implement parsePayload() method.
    }

    /**
     * @inheritDoc
     */
    function createOrder(bool $prediction = false): array
    {
        return [];
    }
}

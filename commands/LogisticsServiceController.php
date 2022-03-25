<?php

namespace app\commands;

use app\extensions\logisticsService\Channel;
use app\extensions\logisticsService\Country;
use app\extensions\logisticsService\Order;
use app\extensions\logisticsService\OrderItem;
use app\extensions\logisticsService\YanWenLogisticsService;

/**
 * 物流服务测试
 *
 * @package app\commands
 */
class LogisticsServiceController extends Controller
{

    public function actionIndex()
    {
        $logistics = new YanWenLogisticsService();
        $channel = new Channel();
        $channels = $logistics->getChannels();
        foreach ($channels as $item) {
            /* @var $item Channel */
            $channel = $item;
            $logistics->setChannel($channel);;
            if ($item->getId() == 174) {
                break;
            } else {
                $this->stdout($channel->getId() . ':' . $channel->getChineseName() . PHP_EOL);
            }
        }

        $country = new Country();
        $countries = $logistics->getCountries();
        foreach ($countries as $item) {
            /* @var $item Country */
            $country = $item;
            break;
        }

        try {
            $order = new Order();
            $order->setNumber(time());
            $order->setChannel($channel);
            $order->setCurrency($country->getCurrency());
            $order->setSenderCountry($country->getChineseName());
            $order->setReceiverName("John");
            $order->setReceiverMobilePhone("1-3333");
            $order->setReceiverCountry($country);
            $order->setReceiverState("Hu Nan");
            $order->setReceiverCity("Chang Sha");
            $order->setReceiverAddress1("Lu Gu niubi xingqiu");
            $order->setReceiverPostcode("410000");
            $order->setRemark(sprintf("This is a test order, [国家: %s, 货币：%s]", "{$country->getCode()} - {$country->getChineseName()}", $country->getCurrency()));
            for ($i = 1; $i <= 2; $i++) {
                $orderItem = new OrderItem();
                $orderItem->setChineseName("中文产品名-$i");
                $orderItem->setEnglishName("English product name-$i");
                $orderItem->setWeight(10);
                $orderItem->setPrice(1.23);
                $orderItem->setQuantity($i);
                $order->setItem($orderItem);
            }

            $logistics->setOrder($order);
            $res = $logistics->createOrder(true);
            var_export($res->toArray());
        } catch (\Exception $e) {
            echo $e->getMessage();
        }
    }

}



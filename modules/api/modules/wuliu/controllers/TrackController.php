<?php

namespace app\modules\api\modules\wuliu\controllers;

use app\extensions\Trackingmore;
use app\modules\api\extensions\AuthController;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\ForbiddenHttpException;

class TrackController extends AuthController
{

    /**
     * @return array
     */
    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    '*' => ['GET'],
                ],
            ],
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'actions' => ['index'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
        ]);
    }

    /**
     * 批量添加查询
     *
     * @param $trackingNumberArr
     * @param $track
     * @param $lang
     * @param $items
     * @throws ForbiddenHttpException
     */
    protected function batchCreateSelect($trackingNumberArr, Trackingmore $track, $lang, &$items)
    {
        $notCreates = [];
        // 不存在则先添加在进行请求
        foreach ($trackingNumberArr as $number) {
            $carrier = $track->detectCarrier($number);
            if (isset($carrier['meta']['code']) && $carrier['meta']['code'] == 200) {
                $code = end($carrier['data'])['code'];
                $notCreates[] = [
                    'tracking_number' => $number,
                    'carrier_code' => $code,
                    'order_id' => $number,
                    'lang' => $lang,
                ];
            } else {
                throw new ForbiddenHttpException($carrier['meta']['message']);
            }
        }
        $trackCreates = $track->createMultipleTracking($notCreates);
        if (isset($trackCreates['meta']['code']) && $trackCreates['meta']['code'] == 200) {
            $trackingNumber = implode(',', $trackingNumberArr);

            sleep(1);
            // 获取刚添加的
            $trackInfoList = $track->getTrackingsList($trackingNumber);

            foreach ($trackInfoList['data']['items'] as $trackInfo) {
                $items[$trackInfo['tracking_number']] = [
                    'tracking_number' => $trackInfo['tracking_number'],
                    'track_info' => $trackInfo['origin_info']['trackinfo'],
                    'last_event' => $trackInfo['lastEvent'],
                ];
            }
        } else {
            throw new ForbiddenHttpException($trackCreates['meta']['message']);
        }
    }

    /**
     * 获取快递物流信息
     *
     * @param $trackingNumber
     * @return mixed
     * @throws ForbiddenHttpException
     */
    public function actionIndex($trackingNumber)
    {
        $track = new \app\extensions\Trackingmore();
        $lang = 'en';
        /**
         * 先查询，如果有直接返回
         * 一个有一个没有，有的存储，没有的先添加在查询返回
         * 两个都没用，则先添加 在查询返回
         */
        $items = [];
        $cache = Yii::$app->cache;
        $cacheName = $trackingNumber . "Index";
        if ($cache->exists($cacheName)) {
            $items = $cache->get($cacheName);
        } else {
            $trackInfoList = $track->getTrackingsList($trackingNumber);
            $trackingNumberArr = explode(',', $trackingNumber);
            if (isset($trackInfoList['meta']['code']) && $trackInfoList['meta']['code'] == 200) {
                // 如果请求成功
                foreach ($trackInfoList['data']['items'] as $trackInfo) {
                    if (in_array($trackInfo['tracking_number'], $trackingNumberArr)) {
                        // 如果存在
                        $items[$trackInfo['tracking_number']] = [
                            'tracking_number' => $trackInfo['tracking_number'],
                            'track_info' => $trackInfo['origin_info']['trackinfo'],
                            'last_event' => $trackInfo['lastEvent'],
                        ];
                        $trackingNumberArr = array_diff($trackingNumberArr, [$trackInfo['tracking_number']]);
                    }
                }
                if ($trackingNumberArr) {
                    // 不存在则先添加再进行请求
                    $this->batchCreateSelect($trackingNumberArr, $track, $lang, $items);
                }
            } else {
                // 不存在则先添加再进行请求
                $this->batchCreateSelect($trackingNumberArr, $track, $lang, $items);
            }
            $cache->set($cacheName, $items, 60 * 30);
        }

        return $items;
    }

}
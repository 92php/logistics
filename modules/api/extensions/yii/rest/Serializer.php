<?php

namespace app\modules\api\extensions\yii\rest;

use app\modules\api\extensions\yii\data\ActiveWithStatisticsDataProvider;

/**
 * 数据序列化（支持统计功能）
 *
 * @package app\modules\api\extensions\yii\rest

 */
class Serializer extends \yii\rest\Serializer
{

    public $statisticsEnvelope = '_statistics';

    protected function serializeDataProvider($dataProvider)
    {
        /* @var $dataProvider \yii\data\BaseDataProvider */
        $result = parent::serializeDataProvider($dataProvider);
        $adp = ActiveWithStatisticsDataProvider::class;
        if ($dataProvider instanceof $adp) {
            if ($this->statisticsEnvelope) {
                $dataProvider->prepare();
                $result[$this->statisticsEnvelope] = call_user_func_array([$dataProvider, 'getStatistics'], []);
            }
        }

        return $result;
    }

}

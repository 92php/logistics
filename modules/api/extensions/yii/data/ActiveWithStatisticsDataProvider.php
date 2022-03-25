<?php

namespace app\modules\api\extensions\yii\data;

use yii\data\ActiveDataProvider;

/**
 * 支持统计功能活动数据提供
 *
 * @package app\modules\api\extensions\yii\data

 */
class ActiveWithStatisticsDataProvider extends ActiveDataProvider
{

    public $statistics;

    /**
     * 统计处理
     *
     * @return array|mixed|string|null
     */
    public function getStatistics()
    {
        if ($this->statistics !== null) {
            if (is_array($this->statistics)) {
                $stat = $this->statistics;
            } elseif (is_callable($this->statistics)) {
                $query = clone $this->query;
                if (($pagination = $this->getPagination()) !== false) {
                    $pagination->totalCount = $this->getTotalCount();
                    if ($pagination->totalCount === 0) {
                        return [];
                    }
                    $query->limit($pagination->getLimit())->offset($pagination->getOffset());
                }
                $stat = call_user_func($this->statistics, $this->getModels(), $query);
            } else {
                $stat = (string) $this->statistics;
            }
        } else {
            $stat = null;
        }

        return $stat;
    }

}

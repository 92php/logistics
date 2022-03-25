<?php

namespace app\commands\classes;

use yii\helpers\Inflector;

/**
 * 包裹数据
 *
 * @package app\commands\classes
 */
class Package
{

    private $key = null;
    private $number;
    private $country;
    private $country_id = 0;
    private $waybill_number = null;

    /**
     * @var string 物流线路名称
     */
    private $logistics_line_name;

    private $logistics_line_id;
    private $shop_id = 0;
    private $third_party_platform_id = 0;
    private $third_party_platform_status = 0;
    private $remark = null;

    private $orders = [];

    /**
     * @return null
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * @param null $key
     */
    public function setKey($key): void
    {
        $this->key = trim($key);
    }

    /**
     * @return mixed
     */
    public function getNumber()
    {
        return $this->number;
    }

    /**
     * @param mixed $number
     */
    public function setNumber($number): void
    {
        $this->number = trim($number);
    }

    /**
     * @return mixed
     */
    public function getCountry()
    {
        return $this->country;
    }

    /**
     * @param mixed $country
     */
    public function setCountry($country): void
    {
        $this->country = trim($country);
    }

    /**
     * @return int
     */
    public function getCountryId(): int
    {
        return $this->country_id;
    }

    /**
     * @param int $country_id
     */
    public function setCountryId(int $country_id): void
    {
        $this->country_id = $country_id;
    }

    /**
     * @return null
     */
    public function getWaybillNumber()
    {
        return $this->waybill_number;
    }

    /**
     * @param null $waybill_number
     */
    public function setWaybillNumber($waybill_number): void
    {
        $this->waybill_number = trim($waybill_number);
    }

    /**
     * @return mixed
     */
    public function getLogisticsLineName()
    {
        return $this->logistics_line_name;
    }

    /**
     * @param mixed $logistics_line_name
     */
    public function setLogisticsLineName($logistics_line_name): void
    {
        $this->logistics_line_name = trim($logistics_line_name);
    }

    /**
     * @return mixed
     */
    public function getLogisticsLineId()
    {
        return $this->logistics_line_id;
    }

    /**
     * @param mixed $logistics_line_id
     */
    public function setLogisticsLineId($logistics_line_id): void
    {
        $this->logistics_line_id = intval($logistics_line_id);
    }

    /**
     * @return int
     */
    public function getShopId(): int
    {
        return $this->shop_id;
    }

    /**
     * @param int $shop_id
     */
    public function setShopId(int $shop_id): void
    {
        $this->shop_id = $shop_id;
    }

    /**
     * @return int
     */
    public function getThirdPartyPlatformId(): int
    {
        return $this->third_party_platform_id;
    }

    /**
     * @param int $third_party_platform_id
     */
    public function setThirdPartyPlatformId(int $third_party_platform_id): void
    {
        $this->third_party_platform_id = $third_party_platform_id;
    }

    /**
     * @return int
     */
    public function getThirdPartyPlatformStatus(): int
    {
        return $this->third_party_platform_status;
    }

    /**
     * @param int $third_party_platform_status
     */
    public function setThirdPartyPlatformStatus(int $third_party_platform_status): void
    {
        $this->third_party_platform_status = $third_party_platform_status;
    }

    /**
     * @return null
     */
    public function getRemark()
    {
        return $this->remark;
    }

    /**
     * @param null $remark
     */
    public function setRemark($remark): void
    {
        $this->remark = $remark;
    }

    /**
     * @return array
     */
    public function getOrders(): array
    {
        return $this->orders;
    }

    /**
     * @param Order $order
     */
    public function setOrders(Order $order): void
    {
        $this->orders[] = $order;
    }

    public function toArray()
    {
        $results = [];
        foreach (get_object_vars($this) as $key => $value) {
            $getter = 'get' . ucfirst(Inflector::id2camel($key, '_'));
            $results[$key] = $this->$getter();
        }

        return $results;
    }

}

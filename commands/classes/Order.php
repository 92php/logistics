<?php

namespace app\commands\classes;

use yii\helpers\Inflector;

/**
 * 订单结构
 *
 * @package app\commands\classes
 */
class Order
{

    private $number = null;

    /**
     * @var int 类型
     */
    private $type = 0;

    private $consignee_name;
    private $consignee_mobile_phone;
    private $consignee_tel;
    private $country_id = 0;
    private $country;
    private $consignee_state;
    private $consignee_city;
    private $consignee_address1;
    private $consignee_address2;
    private $consignee_postcode;
    private $total_amount = 0;
    private $third_party_platform_id = 0;
    private $third_party_platform_status = 0;
    private $platform_id = 0;
    private $shop_id = 0;
    private $product_type = \app\modules\admin\modules\g\models\Shop::PRODUCT_TYPE_GENERAL;
    private $place_order_at;
    private $payment_at = null;
    private $remark = null;

    /**
     * @var Package 包裹
     */
    private $package;

    /**
     * @var array 订单项
     */
    private $items = [];

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
    public function setNumber($number)
    {
        $this->number = trim($number);
    }

    /**
     * @return int
     */
    public function getType(): int
    {
        return $this->type;
    }

    /**
     * @param int $type
     */
    public function setType(int $type): void
    {
        $this->type = $type;
    }

    /**
     * @return mixed
     */
    public function getConsigneeName()
    {
        return $this->consignee_name;
    }

    /**
     * @param mixed $consignee_name
     */
    public function setConsigneeName($consignee_name)
    {
        $this->consignee_name = trim($consignee_name);
    }

    /**
     * @return mixed
     */
    public function getConsigneeMobilePhone()
    {
        return $this->consignee_mobile_phone;
    }

    /**
     * @param mixed $consignee_mobile_phone
     */
    public function setConsigneeMobilePhone($consignee_mobile_phone)
    {
        $this->consignee_mobile_phone = trim($consignee_mobile_phone);
    }

    /**
     * @return mixed
     */
    public function getConsigneeTel()
    {
        return $this->consignee_tel;
    }

    /**
     * @param mixed $consignee_tel
     */
    public function setConsigneeTel($consignee_tel)
    {
        $this->consignee_tel = trim($consignee_tel);
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
     * @return mixed
     */
    public function getCountry()
    {
        return $this->country;
    }

    /**
     * @param mixed $country
     */
    public function setCountry($country)
    {
        $this->country = trim($country);
    }

    /**
     * @return mixed
     */
    public function getConsigneeState()
    {
        return $this->consignee_state;
    }

    /**
     * @param mixed $consignee_state
     */
    public function setConsigneeState($consignee_state)
    {
        $this->consignee_state = trim($consignee_state);
    }

    /**
     * @return mixed
     */
    public function getConsigneeCity()
    {
        return $this->consignee_city;
    }

    /**
     * @param mixed $consignee_city
     */
    public function setConsigneeCity($consignee_city)
    {
        $this->consignee_city = trim($consignee_city);
    }

    /**
     * @return mixed
     */
    public function getConsigneeAddress1()
    {
        return $this->consignee_address1;
    }

    /**
     * @param mixed $consignee_address1
     */
    public function setConsigneeAddress1($consignee_address1)
    {
        $this->consignee_address1 = trim($consignee_address1);
    }

    /**
     * @return mixed
     */
    public function getConsigneeAddress2()
    {
        return $this->consignee_address2;
    }

    /**
     * @param mixed $consignee_address2
     */
    public function setConsigneeAddress2($consignee_address2)
    {
        $this->consignee_address2 = trim($consignee_address2);
    }

    /**
     * @return mixed
     */
    public function getConsigneePostcode()
    {
        return $this->consignee_postcode;
    }

    /**
     * @param mixed $consignee_postcode
     */
    public function setConsigneePostcode($consignee_postcode)
    {
        $this->consignee_postcode = $consignee_postcode;
    }

    /**
     * @return mixed
     */
    public function getTotalAmount()
    {
        return $this->total_amount;
    }

    /**
     * @param mixed $total_amount
     */
    public function setTotalAmount($total_amount)
    {
        $this->total_amount = floatval($total_amount);
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
        $this->third_party_platform_id = intval($third_party_platform_id);
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
        $this->third_party_platform_status = intval($third_party_platform_status);
    }

    /**
     * @return mixed
     */
    public function getPlatformId()
    {
        return $this->platform_id;
    }

    /**
     * @param mixed $platform_id
     */
    public function setPlatformId($platform_id)
    {
        $this->platform_id = $platform_id;
    }

    /**
     * @return mixed
     */
    public function getShopId()
    {
        return $this->shop_id;
    }

    /**
     * @param mixed $shop_id
     */
    public function setShopId($shop_id)
    {
        $this->shop_id = $shop_id;
    }

    /**
     * @return int
     */
    public function getProductType(): int
    {
        return $this->product_type;
    }

    /**
     * @param int $product_type
     */
    public function setProductType(int $product_type): void
    {
        $this->product_type = intval($product_type);
    }

    /**
     * @return mixed
     */
    public function getPlaceOrderAt()
    {
        return $this->place_order_at;
    }

    /**
     * @param mixed $place_order_at
     */
    public function setPlaceOrderAt($place_order_at)
    {
        $this->place_order_at = $place_order_at;
    }

    /**
     * @return mixed
     */
    public function getPaymentAt()
    {
        return $this->payment_at;
    }

    /**
     * @param mixed $payment_at
     */
    public function setPaymentAt($payment_at)
    {
        $this->payment_at = $payment_at;
    }

    /**
     * @return mixed
     */
    public function getRemark()
    {
        return $this->remark;
    }

    /**
     * @param mixed $remark
     */
    public function setRemark($remark)
    {
        $this->remark = trim($remark);
    }

    /**
     * @return mixed
     */
    public function getPackage(): Package
    {
        return $this->package ?: new Package();
    }

    /**
     * @param mixed $package
     */
    public function setPackage(Package $package): void
    {
        $this->package = $package;
    }

    public function getItems()
    {
        return $this->items;
    }

    public function setItem(OrderItem $item)
    {
        $this->items[] = $item;
    }

    public function setItems(array $items)
    {
        $this->items = $items;
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
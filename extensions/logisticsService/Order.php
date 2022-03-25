<?php

namespace app\extensions\logisticsService;

use ErrorException;

/**
 * 订单数据
 *
 * 该订单为提交给物流商创建物流信息所用，数据来源于系统中订单的相关信息
 *
 * @package app\extensions\logisticsService
 */
class Order
{

    private $number = '';
    private $trackingNumber = '';
    private $weight = 0;
    private $quantity = 0;
    private $amount = 0;
    private $currency = Country::CURRENCY_USD;

    private $channel;

    private $senderName;
    private $senderCountry;
    private $senderAddress;

    private $receiverName;
    private $receiverEmail;
    private $receiverMobilePhone;
    private $receiverTel;
    private $receiverCountry;
    private $receiverState;
    private $receiverCity;
    private $receiverPostcode;
    private $receiverAddress1;
    private $receiverAddress2;

    private $items = [];
    private $remark;

    private function calc()
    {
        $weight = 0;
        $quantity = 0;
        $amount = 0;
        foreach ($this->items as $item) {
            /* @var $item OrderItem */
            $weight += $item->getWeight();
            $quantity += $item->getQuantity();
            $amount += $item->getQuantity() * $item->getPrice();
        }
        $this->weight = $weight;
        $this->quantity = $quantity;
        $this->amount = $amount;
    }

    /**
     * @return string
     */
    public function getNumber(): string
    {
        return $this->number;
    }

    /**
     * @param string $number
     */
    public function setNumber(string $number): void
    {
        $this->number = $number;
    }

    /**
     * @return string
     */
    public function getTrackingNumber(): string
    {
        return $this->trackingNumber;
    }

    /**
     * @param string $TrackingNumber
     */
    public function setTrackingNumber(string $TrackingNumber): void
    {
        $this->trackingNumber = trim($TrackingNumber);
    }

    /**
     * @return int
     */
    public function getWeight(): int
    {
        return $this->weight;
    }

    /**
     * @param int $weight
     */
    public function setWeight(int $weight): void
    {
        $this->weight = $weight;
    }

    /**
     * @return int
     */
    public function getQuantity(): int
    {
        return $this->quantity;
    }

    /**
     * @param int $quantity
     */
    public function setQuantity(int $quantity): void
    {
        $this->quantity = $quantity;
    }

    /**
     * @return int
     */
    public function getAmount(): int
    {
        return $this->amount;
    }

    /**
     * @param int $amount
     */
    public function setAmount(int $amount): void
    {
        $this->amount = $amount;
    }

    /**
     * @return string
     */
    public function getCurrency(): string
    {
        return $this->currency ?: Country::CURRENCY_USD;
    }

    /**
     * @param string $currency
     */
    public function setCurrency($currency): void
    {
        $this->currency = $currency;
    }

    /**
     * @return mixed
     */
    public function getChannel(): Channel
    {
        return $this->channel;
    }

    /**
     * @param mixed $channel
     */
    public function setChannel(Channel $channel): void
    {
        $this->channel = $channel;
    }

    /**
     * @return mixed
     */
    public function getSenderName()
    {
        return $this->senderName;
    }

    /**
     * @param mixed $senderName
     */
    public function setSenderName($senderName): void
    {
        $this->senderName = $senderName;
    }

    /**
     * @return mixed
     */
    public function getSenderCountry()
    {
        return $this->senderCountry;
    }

    /**
     * @param mixed $senderCountry
     */
    public function setSenderCountry($senderCountry): void
    {
        $this->senderCountry = $senderCountry;
    }

    /**
     * @return mixed
     */
    public function getSenderAddress()
    {
        return $this->senderAddress;
    }

    /**
     * @param mixed $senderAddress
     */
    public function setSenderAddress($senderAddress): void
    {
        $this->senderAddress = $senderAddress;
    }

    /**
     * @return mixed
     */
    public function getReceiverName()
    {
        return $this->receiverName;
    }

    /**
     * @param mixed $receiverName
     */
    public function setReceiverName($receiverName): void
    {
        $this->receiverName = $receiverName;
    }

    /**
     * @return mixed
     */
    public function getReceiverEmail()
    {
        return $this->receiverEmail;
    }

    /**
     * @param mixed $receiverEmail
     */
    public function setReceiverEmail($receiverEmail): void
    {
        $this->receiverEmail = $receiverEmail;
    }

    /**
     * @return mixed
     */
    public function getReceiverMobilePhone()
    {
        return $this->receiverMobilePhone;
    }

    /**
     * @param mixed $receiverMobilePhone
     */
    public function setReceiverMobilePhone($receiverMobilePhone): void
    {
        $this->receiverMobilePhone = $receiverMobilePhone;
    }

    /**
     * @return mixed
     */
    public function getReceiverTel()
    {
        return $this->receiverTel;
    }

    /**
     * @param mixed $receiverTel
     */
    public function setReceiverTel($receiverTel): void
    {
        $this->receiverTel = $receiverTel;
    }

    /**
     * @return mixed
     */
    public function getReceiverCountry(): Country
    {
        return $this->receiverCountry;
    }

    /**
     * @param Country $receiverCountry
     */
    public function setReceiverCountry(Country $receiverCountry): void
    {
        $this->receiverCountry = $receiverCountry;
    }

    /**
     * @return mixed
     */
    public function getReceiverState()
    {
        return $this->receiverState;
    }

    /**
     * @param mixed $receiverState
     */
    public function setReceiverState($receiverState): void
    {
        $this->receiverState = $receiverState;
    }

    /**
     * @return mixed
     */
    public function getReceiverCity()
    {
        return $this->receiverCity;
    }

    /**
     * @param mixed $receiverCity
     */
    public function setReceiverCity($receiverCity): void
    {
        $this->receiverCity = $receiverCity;
    }

    /**
     * @return mixed
     */
    public function getReceiverPostcode()
    {
        return $this->receiverPostcode;
    }

    /**
     * @param mixed $receiverPostcode
     */
    public function setReceiverPostcode($receiverPostcode): void
    {
        $this->receiverPostcode = $receiverPostcode;
    }

    /**
     * @return mixed
     */
    public function getReceiverAddress1()
    {
        return $this->receiverAddress1;
    }

    /**
     * @param mixed $receiverAddress1
     */
    public function setReceiverAddress1($receiverAddress1): void
    {
        $this->receiverAddress1 = $receiverAddress1;
    }

    /**
     * @return mixed
     */
    public function getReceiverAddress2()
    {
        return $this->receiverAddress2;
    }

    /**
     * @param mixed $receiverAddress2
     */
    public function setReceiverAddress2($receiverAddress2): void
    {
        $this->receiverAddress2 = $receiverAddress2;
    }

    /**
     * @return array
     */
    public function getItems(): array
    {
        return $this->items;
    }

    public function setItem(OrderItem $orderItem): void
    {
        $this->items[] = $orderItem;
        $this->calc();
    }

    /**
     * @param array $items
     * @throws ErrorException
     */
    public function setItems(array $items): void
    {
        foreach ($items as $item) {
            if ($item instanceof OrderItem) {
                $this->setItem($item);
            } else {
                throw new ErrorException(var_export($item, true) . ' is not instanceof ' . OrderItem::class);
            }
        }
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
    public function setRemark($remark): void
    {
        $this->remark = $remark;
    }

}
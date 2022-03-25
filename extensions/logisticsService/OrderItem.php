<?php

namespace app\extensions\logisticsService;

/**
 * 订单项数据
 *
 * @package app\extensions\logisticsService
 */
class OrderItem
{

    private $chinese_name;
    private $english_name;
    private $weight = 0;
    private $quantity = 0;
    private $price = 0;
    private $amount = 0;

    /**
     * @return mixed
     */
    public function getChineseName()
    {
        return $this->chinese_name;
    }

    /**
     * @param mixed $chinese_name
     */
    public function setChineseName($chinese_name): void
    {
        $this->chinese_name = trim($chinese_name);
    }

    /**
     * @return mixed
     */
    public function getEnglishName()
    {
        return $this->english_name;
    }

    /**
     * @param mixed $english_name
     */
    public function setEnglishName($english_name): void
    {
        $this->english_name = trim($english_name);
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
        $quantity && $this->amount = $this->quantity * $this->price;
    }

    /**
     * @return float
     */
    public function getPrice(): float
    {
        return $this->price;
    }

    /**
     * @param float $price
     */
    public function setPrice(float $price): void
    {
        $this->price = $price;
        $price > 0 && $this->amount = $this->quantity * $this->price;
    }

    /**
     * @return float
     */
    public function getAmount(): float
    {
        return $this->amount;
    }

}
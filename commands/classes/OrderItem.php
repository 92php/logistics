<?php

namespace app\commands\classes;

use yii\helpers\Inflector;

/**
 * 订单项目结构
 *
 * @package app\commands\classes
 */
class OrderItem
{

    private $order_id;
    private $image = '';
    private $product_id = 0;
    private $key;
    private $sku;
    private $product_name;
    private $extend = [];
    private $ignored = false;
    private $quantity = 0;
    private $sale_price = 0;
    private $pid;
    private $remark;

    /**
     * @return mixed
     */
    public function getOrderId()
    {
        return $this->order_id;
    }

    /**
     * @param mixed $order_id
     */
    public function setOrderId($order_id)
    {
        $this->order_id = intval($order_id);
    }

    /**
     * @return mixed
     */
    public function getImage()
    {
        return $this->image;
    }

    /**
     * @param mixed $image
     */
    public function setImage($image)
    {
        $this->image = trim($image);
    }

    /**
     * @return mixed
     */
    public function getProductId()
    {
        return $this->product_id;
    }

    /**
     * @param mixed $product_id
     */
    public function setProductId($product_id)
    {
        $this->product_id = intval($product_id);
    }

    /**
     * @return mixed
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * @param mixed $key
     */
    public function setKey($key): void
    {
        $this->key = trim($key);
    }

    /**
     * @return mixed
     */
    public function getSku()
    {
        return $this->sku;
    }

    /**
     * @param mixed $sku
     */
    public function setSku($sku)
    {
        $this->sku = trim($sku);
    }

    /**
     * @return mixed
     */
    public function getProductName()
    {
        return $this->product_name;
    }

    /**
     * @param mixed $product_name
     */
    public function setProductName($product_name)
    {
        $this->product_name = trim($product_name);
    }

    /**
     * @return bool
     */
    public function getIgnored(): bool
    {
        return $this->ignored;
    }

    /**
     * @param bool $ignored
     */
    public function setIgnored(bool $ignored): void
    {
        $this->ignored = $ignored;
    }

    /**
     * @return mixed
     */
    public function getExtend()
    {
        return $this->extend;
    }

    /**
     * @param mixed $extend
     */
    public function setExtend(array $extend)
    {
        $this->extend = $extend;
    }

    /**
     * @return mixed
     */
    public function getQuantity()
    {
        return $this->quantity;
    }

    /**
     * @param mixed $quantity
     */
    public function setQuantity($quantity)
    {
        $this->quantity = intval($quantity);
    }

    /**
     * @return mixed
     */
    public function getSalePrice()
    {
        return $this->sale_price;
    }

    /**
     * @param mixed $sale_price
     */
    public function setSalePrice($sale_price)
    {
        $this->sale_price = floatval($sale_price);
    }

    /**
     * @return mixed
     */
    public function getPid()
    {
        return $this->pid;
    }

    /**
     * @param mixed $pid
     */
    public function setPid($pid): void
    {
        $this->pid = trim($pid);
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
        $this->remark = $remark;
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
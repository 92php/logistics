<?php

namespace app\commands\classes;

/**
 * 店铺信息
 *
 * @package app\commands\classes
 */
class Shop
{

    private $id = 0;
    private $platform_id = 0;
    private $sign;

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     */
    public function setId($id): void
    {
        $this->id = intval($id);
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
    public function setPlatformId($platform_id): void
    {
        $this->platform_id = intval($platform_id);
    }

    /**
     * @return mixed
     */
    public function getSign()
    {
        return $this->sign;
    }

    /**
     * @param mixed $sign
     */
    public function setSign($sign): void
    {
        $this->sign = trim($sign);
    }

}
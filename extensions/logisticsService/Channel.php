<?php

namespace app\extensions\logisticsService;

/**
 * 物流渠道类
 *
 * @package app\extensions\logisticsService
 */
class Channel
{

    private $id;
    private $code;
    private $chinese_name;
    private $english_name;
    private $status;

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
        $this->id = trim($id);
    }

    /**
     * @return mixed
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @param mixed $code
     */
    public function setCode($code): void
    {
        $this->code = trim($code);
    }

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
     * @return mixed
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param mixed $status
     */
    public function setStatus($status): void
    {
        $this->status = $status;
    }

}

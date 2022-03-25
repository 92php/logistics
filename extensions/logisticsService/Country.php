<?php

namespace app\extensions\logisticsService;

/**
 * 发货国家
 *
 * @package app\extensions\logisticsService
 */
class Country
{

    CONST CODE_CN = 'CN'; // 中国
    CONST CODE_USA = 'USA'; // 美国
    CONST CODE_CO = 'CO'; // 哥伦比亚

    /**
     * 货币符号
     *
     * @todo 需要完善
     * @see http://www.uaec-expo.com/hb.html
     */
    const CURRENCY_RMB = 'RMB';
    const CURRENCY_JPY = 'JPY';
    const CURRENCY_GBP = 'GBP';
    const CURRENCY_CHF = 'CHF';
    const CURRENCY_CAD = 'CAD';
    const CURRENCY_HKD = 'HKD';
    const CURRENCY_FIM = 'FIM';
    const CURRENCY_IEP = 'IEP';
    const CURRENCY_LUF = 'LUF';
    const CURRENCY_PTE = 'PTE';
    const CURRENCY_IDR = 'IDR';
    const CURRENCY_NZD = 'NZD';
    const CURRENCY_SUR = 'SUR';
    const CURRENCY_KRW = 'KRW';
    const CURRENCY_USD = 'USD';
    const CURRENCY_EUR = 'EUR';
    const CURRENCY_DEM = 'DEM';
    const CURRENCY_FRF = 'FRF';
    const CURRENCY_AUD = 'AUD';
    const CURRENCY_ATS = 'ATS';
    const CURRENCY_BEF = 'BEF';
    const CURRENCY_ITL = 'ITL';
    const CURRENCY_NLG = 'NLG';
    const CURRENCY_ESP = 'ESP';
    const CURRENCY_MYR = 'MYR';
    const CURRENCY_PHP = 'PHP';
    const CURRENCY_SGD = 'SGD';
    const CURRENCY_THB = 'THB';
    const CURRENCY_COP = 'COP';

    /**
     *
     * @var array 国家货币对应关系
     */
    private $codeCurrencyMap = [
        self::CODE_CN => self::CURRENCY_RMB,
        self::CODE_USA => self::CURRENCY_USD,
        self::CODE_CO => self::CURRENCY_COP,
    ];

    private $id;
    private $code;
    private $chinese_name;
    private $english_name;
    private $currency;
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
        $this->id = $id;
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
        $this->code = strtoupper($code);
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
        $this->chinese_name = $chinese_name;
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
        $this->english_name = $english_name;
    }

    /**
     * @return mixed
     */
    public function getCurrency()
    {
        $s = $this->currency;

        return $s ? $s : $this->codeCurrencyMap[$this->code] ?? null;
    }

    /**
     * @param mixed $currency
     */
    public function setCurrency($currency): void
    {
        $this->currency = trim($currency);
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

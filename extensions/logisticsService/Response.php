<?php

namespace app\extensions\logisticsService;

/**
 * 物流服务统一返回格式
 *
 * @package app\extensions\logisticsService
 */
class Response
{

    const ERROR_CODE_0 = 0;
    const ERROR_CODE_200 = 200;
    const ERROR_CODE_401 = 401;
    const ERROR_CODE_404 = 404;
    const ERROR_CODE_500 = 500;

    private $success = false;
    private $error_code = self::ERROR_CODE_0;
    private $error_message = '';
    private $data = [];

    /**
     * @return bool
     */
    public function isSuccess(): bool
    {
        return $this->success;
    }

    /**
     * @param bool $success
     */
    public function setSuccess(bool $success): void
    {
        $this->success = $success;
    }

    /**
     * @return int
     */
    public function getErrorCode(): int
    {
        return $this->error_code;
    }

    /**
     * @param int $code
     */
    public function setErrorCode(int $code): void
    {
        $this->error_code = $code;
    }

    /**
     * @return string
     */
    public function getErrorMessage(): string
    {
        return $this->error_message;
    }

    /**
     * @param string $message
     */
    public function setErrorMessage(string $message): void
    {
        $this->error_message = trim($message);
        $this->setSuccess(false);
    }

    /**
     * @return array
     */
    public function getData(): array
    {
        return $this->data;
    }

    /**
     * @param array $data
     */
    public function setData(array $data): void
    {
        $this->data = $data;
    }

    public function toArray()
    {
        $resp = [
            'success' => $this->isSuccess(),
        ];
        if ($this->isSuccess()) {
            $resp['data'] = $this->getData();
        } else {
            $resp['error'] = [
                'code' => $this->getErrorCode(),
                'message' => $this->getErrorMessage(),
            ];
        }

        return $resp;
    }

    public function __toString(): string
    {
        return json_encode($this->toArray(), JSON_UNESCAPED_UNICODE);
    }

}
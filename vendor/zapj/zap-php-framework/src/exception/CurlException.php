<?php

namespace zap\exception;

class CurlException extends \RuntimeException
{
    /**
     * CURL 错误码
     */
    protected int $curlErrno;

    /**
     * @param string          $message   错误消息
     * @param int             $curlErrno CURL 错误码
     * @param int             $code      内部错误码
     * @param \Throwable|null $previous  前一个异常
     */
    public function __construct(string $message = '', int $curlErrno = 0, int $code = 0, \Throwable $previous = null)
    {
        $this->curlErrno = $curlErrno;
        if (empty($message) && $curlErrno > 0) {
            $message = 'cURL 错误 (' . $curlErrno . '): ' . curl_strerror($curlErrno);
        }
        parent::__construct($message ?: 'cURL 请求失败', $code, $previous);
    }

    /**
     * 获取 CURL 错误码
     */
    public function getCurlErrno(): int
    {
        return $this->curlErrno;
    }
}
<?php

namespace zap\exception;

class HttpException extends \RuntimeException
{
    /**
     * HTTP 状态码
     */
    protected int $statusCode;

    /**
     * 响应头
     */
    protected array $headers = [];

    /**
     * @param int    $statusCode HTTP 状态码
     * @param string $message    错误消息
     * @param int    $code       内部错误码
     * @param \Throwable|null $previous 前一个异常
     */
    public function __construct(
        int $statusCode = 500,
        string $message = '',
        int $code = 0,
        \Throwable $previous = null
    ) {
        $this->statusCode = $statusCode;
        parent::__construct($message ?: $this->defaultMessage($statusCode), $code, $previous);
    }

    /**
     * 获取 HTTP 状态码
     */
    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    /**
     * 获取响应头
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }

    /**
     * 设置响应头
     *
     * @return $this
     */
    public function withHeaders(array $headers): self
    {
        $this->headers = array_merge($this->headers, $headers);
        return $this;
    }

    /**
     * 默认状态码消息
     */
    protected function defaultMessage(int $statusCode): string
    {
        $messages = [
            400 => '错误的请求',
            401 => '未授权，请先登录',
            403 => '禁止访问',
            404 => '请求的资源未找到',
            405 => '请求方法不允许',
            408 => '请求超时',
            419 => '页面已过期',
            422 => '数据验证失败',
            429 => '请求过于频繁，请稍后重试',
            500 => '服务器内部错误',
            502 => '网关错误',
            503 => '服务暂不可用',
            504 => '网关超时',
        ];
        return $messages[$statusCode] ?? 'HTTP 错误 ' . $statusCode;
    }
}
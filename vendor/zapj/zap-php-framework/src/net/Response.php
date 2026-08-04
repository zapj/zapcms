<?php

namespace zap\net;

class Response
{
    /** @var string|bool 响应体 */
    protected $body;

    /** @var array curl_getinfo 返回的信息 */
    protected $info;

    public function __construct($body, array $info = [])
    {
        $this->body = $body;
        $this->info = $info;
    }

    /**
     * 获取响应体文本
     */
    public function body(): string
    {
        return (string) $this->body;
    }

    /**
     * 返回原始字符串（兼容旧用法直接当字符串用）
     */
    public function __toString(): string
    {
        return $this->body();
    }

    /**
     * 解析 JSON 响应
     *
     * @param bool $assoc 是否返回关联数组
     * @return array|object|null
     */
    public function json(bool $assoc = true)
    {
        return json_decode($this->body, $assoc);
    }

    /**
     * HTTP 状态码
     */
    public function status(): int
    {
        return (int) ($this->info['http_code'] ?? 0);
    }

    /**
     * 请求是否成功 (2xx)
     */
    public function ok(): bool
    {
        $status = $this->status();
        return $status >= 200 && $status < 300;
    }

    /**
     * 客户端错误 (4xx)
     */
    public function clientError(): bool
    {
        $status = $this->status();
        return $status >= 400 && $status < 500;
    }

    /**
     * 服务端错误 (5xx)
     */
    public function serverError(): bool
    {
        return $this->status() >= 500;
    }

    /**
     * 总耗时（秒）
     */
    public function totalTime(): float
    {
        return (float) ($this->info['total_time'] ?? 0);
    }

    /**
     * Content-Type
     */
    public function contentType(): string
    {
        return (string) ($this->info['content_type'] ?? '');
    }

    /**
     * 响应头大小
     */
    public function headerSize(): int
    {
        return (int) ($this->info['header_size'] ?? 0);
    }

    /**
     * 获取完整 curl_getinfo 信息
     */
    public function info(): array
    {
        return $this->info;
    }

    /**
     * 获取指定 info 字段
     */
    public function getInfo(string $key, $default = null)
    {
        return $this->info[$key] ?? $default;
    }

    /**
     * 获取最终有效 URL（跟随重定向后）
     */
    public function effectiveUrl(): string
    {
        return (string) ($this->info['url'] ?? '');
    }
}

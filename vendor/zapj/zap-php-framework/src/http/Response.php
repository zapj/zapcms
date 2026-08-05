<?php

namespace zap\http;

class Response
{
    /** @var int HTTP 状态码 */
    protected int $statusCode = 200;

    /** @var string 响应内容 */
    public string $content = '';

    /** @var array 响应头 */
    protected array $headers = [];

    /** @var bool 是否已发送 */
    protected bool $sent = false;

    /** @var array 状态码→状态文本映射 */
    protected static array $statusTexts = [
        100 => 'Continue',
        101 => 'Switching Protocols',
        200 => 'OK',
        201 => 'Created',
        202 => 'Accepted',
        204 => 'No Content',
        301 => 'Moved Permanently',
        302 => 'Found',
        303 => 'See Other',
        304 => 'Not Modified',
        307 => 'Temporary Redirect',
        308 => 'Permanent Redirect',
        400 => 'Bad Request',
        401 => 'Unauthorized',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        406 => 'Not Acceptable',
        409 => 'Conflict',
        410 => 'Gone',
        422 => 'Unprocessable Entity',
        429 => 'Too Many Requests',
        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable',
    ];

    public function __construct($content = null, int $statusCode = 200)
    {
        $this->setContent($content);
        $this->setStatusCode($statusCode);
    }

    // ───────────────────── 状态码 ─────────────────────

    public function setStatusCode(int $code): self
    {
        $this->statusCode = $code;
        return $this;
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    public function getStatusText(): string
    {
        return self::$statusTexts[$this->statusCode] ?? '';
    }

    // ───────────────────── 内容 ─────────────────────

    public function setContent($content): self
    {
        if ($content !== null) {
            if (is_array($content) || is_object($content)) {
                $this->content = json_encode($content, JSON_UNESCAPED_UNICODE);
            } else {
                $this->content = (string)$content;
            }
        }
        return $this;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    // ───────────────────── 响应类型 ─────────────────────

    /**
     * 以 JSON 格式输出并发送
     */
    public function withJson(): void
    {
        if (!$this->sent) {
            $this->header('Content-Type', 'application/json; charset=utf-8');
            $this->send();
        }
    }

    /** 标记为 JSON 响应（链式调用，不自动发送） */
    public function asJson(): self
    {
        $this->header('Content-Type', 'application/json; charset=utf-8');
        return $this;
    }

    /** 发送 JSON 响应并退出（静态，兼容 Response::json([...])） */
    public static function json($data = null, int $statusCode = 200): void
    {
        $response = new self($data, $statusCode);
        $response->asJson()->send();
        exit;
    }

    /** 标记为 HTML 响应 */
    public function html(): self
    {
        $this->header('Content-Type', 'text/html; charset=utf-8');
        return $this;
    }

    /** 标记为纯文本响应 */
    public function text(): self
    {
        $this->header('Content-Type', 'text/plain; charset=utf-8');
        return $this;
    }

    // ───────────────────── 便捷响应工厂 ─────────────────────

    public static function noContent(): self
    {
        $r = new self('', 204);
        $r->send();
        return $r;
    }

    public static function notFound(string $message = 'Not Found'): self
    {
        return (new self(['error' => $message], 404))->asJson();
    }

    public static function forbidden(string $message = 'Forbidden'): self
    {
        return (new self(['error' => $message], 403))->asJson();
    }

    public static function unauthorized(string $message = 'Unauthorized'): self
    {
        return (new self(['error' => $message], 401))->asJson();
    }

    public static function badRequest(string $message = 'Bad Request'): self
    {
        return (new self(['error' => $message], 400))->asJson();
    }

    public static function created($data = null): self
    {
        return (new self($data, 201))->asJson();
    }

    public static function ok($data = null): self
    {
        return (new self($data, 200))->asJson();
    }

    // ───────────────────── 响应头 ─────────────────────

    public function header(string $name, string $value): self
    {
        $this->headers[$name] = $value;
        return $this;
    }

    public function withHeaders(array $headers): self
    {
        foreach ($headers as $name => $value) {
            $this->headers[$name] = $value;
        }
        return $this;
    }

    public function getHeaders(): array
    {
        return $this->headers;
    }

    // ───────────────────── Cookie ─────────────────────

    /**
     * 设置 Cookie（PHP 7.3+ 数组语法）
     */
    public function cookie(
        string $name,
        string $value = '',
        int $expire = 0,
        string $path = '/',
        string $domain = '',
        bool $secure = false,
        bool $httpOnly = true,
        string $sameSite = 'Lax'
    ): self {
        setcookie($name, $value, [
            'expires'  => $expire,
            'path'     => $path,
            'domain'   => $domain,
            'secure'   => $secure,
            'httponly' => $httpOnly,
            'samesite' => $sameSite,
        ]);
        return $this;
    }

    // ───────────────────── 重定向 ─────────────────────

    /**
     * 重定向
     *
     * @param string $url  目标 URL
     * @param int    $code 重定向状态码 (301/302/303/307/308)
     */
    public static function redirect(string $url, int $code = 302): void
    {
        $response = new self();
        $response->setStatusCode($code);
        $response->header('Location', $url);
        $response->send();
        exit;
    }

    // ───────────────────── 下载 ─────────────────────

    /**
     * 发送文件下载
     */
    public static function download(string $filePath, string $filename = null): void
    {
        if (!is_file($filePath)) {
            self::notFound('File not found');
            exit;
        }

        $filename = $filename ?? basename($filePath);
        $sanitized = str_replace(['"', "'", '\\', '/', "\0", "\n", "\r"], '_', $filename);

        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . $sanitized . '"');
        header('Content-Length: ' . filesize($filePath));
        header('Cache-Control: must-revalidate');

        readfile($filePath);
        exit;
    }

    // ───────────────────── 发送 ─────────────────────

    /**
     * 发送响应
     */
    public function send(): self
    {
        if ($this->sent) {
            return $this;
        }

        // 发送状态码
        if (!headers_sent()) {
            http_response_code($this->statusCode);
        }

        // 发送自定义响应头
        foreach ($this->headers as $name => $value) {
            if (!headers_sent()) {
                header("{$name}: {$value}");
            }
        }

        // 输出内容
        echo $this->content;
        $this->sent = true;

        return $this;
    }

    /**
     * 返回响应字符串（不输出）
     */
    public function __toString(): string
    {
        return $this->content;
    }
}

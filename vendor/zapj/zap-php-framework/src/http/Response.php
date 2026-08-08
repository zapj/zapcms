<?php

/**
 * ZAP PHP Framework — HTTP Response
 *
 * 统一 HTTP 响应封装，支持：
 *   - 纯文本 / JSON / HTML 响应
 *   - 重定向 + Flash 消息链式调用
 *   - 文件下载
 *   - HTTP 错误快捷方法 (404/403/500)
 *   - 自动发送（析构时检测，避免忘记 send）
 */

namespace zap\http;

class Response
{
    /** @var string|null 响应内容 */
    protected ?string $content = null;

    /** @var int HTTP 状态码 */
    protected int $statusCode;

    /** @var array<string, string> 响应头 */
    protected array $headers = [];

    /** @var bool 是否已发送 */
    protected bool $sent = false;

    /** @var bool 是否允许析构时自动发送（默认开启，可在 CLI/测试中关闭） */
    protected static bool $autoSendEnabled = true;

    /**
     * @param mixed       $content    响应内容 (字符串/null/数组)
     * @param int         $statusCode HTTP 状态码
     * @param array       $headers    附加响应头 ['X-Custom' => 'value']
     */
    public function __construct(
        $content = null,
        int $statusCode = 200,
        array $headers = []
    ) {
        if (is_array($content)) {
            $this->content = json_encode($content, JSON_UNESCAPED_UNICODE);
            $this->headers['Content-Type'] = 'application/json; charset=utf-8';
        } else {
            $this->content = (string)$content;
            $this->headers['Content-Type'] = 'text/html; charset=utf-8';
        }

        $this->statusCode = $statusCode;

        foreach ($headers as $name => $value) {
            $this->headers[$name] = $value;
        }
    }

    // ─────────────────── 工厂方法 ───────────────────

    /**
     * 纯文本响应
     */
    public static function text(string $content, int $code = 200): self
    {
        $instance = new self($content, $code);
        $instance->headers['Content-Type'] = 'text/plain; charset=utf-8';
        return $instance;
    }

    /**
     * HTML 响应
     */
    public static function html(string $content, int $code = 200): self
    {
        $instance = new self($content, $code);
        $instance->headers['Content-Type'] = 'text/html; charset=utf-8';
        return $instance;
    }

    /**
     * JSON 响应（快捷方法，立即发送并退出）
     */
    public static function json(array $data = [], int $code = 200): void
    {
        $instance = new self($data, $code);
        $instance->send();
        exit;
    }

    /**
     * 重定向 — 返回实例以支持链式 .with() 追加 Flash 消息
     *
     * 析构函数会自动发送并终止脚本，无需手动 exit。
     *
     * @param string $url  目标 URL
     * @param int    $code HTTP 状态码 (301/302)
     */
    public static function redirect(string $url, int $code = 302): self
    {
        $instance = new self(null, $code);
        $instance->headers['Location'] = $url;
        $instance->headers['Content-Type'] = 'text/html; charset=utf-8';
        return $instance;
    }

    /**
     * 重定向回上一页
     *
     * @param string $fallback 无 Referer 时的回退 URL，默认 '/'
     * @param int    $code     302
     */
    public static function back(string $fallback = '/', int $code = 302): self
    {
        $url = $_SERVER['HTTP_REFERER'] ?? $fallback;
        return static::redirect($url, $code);
    }

    /**
     * 204 No Content（成功但无响应体）
     */
    public static function noContent(): self
    {
        return new self(null, 204);
    }

    /**
     * 文件下载响应
     *
     * @param string      $filePath 文件绝对路径
     * @param string|null $fileName 下载时的文件名，默认使用原文件名
     */
    public static function download(string $filePath, ?string $fileName = null): self
    {
        if (!is_file($filePath)) {
            return static::notFound('文件不存在: ' . basename($filePath));
        }

        $fileName ??= basename($filePath);

        $instance = new self(null, 200);
        $instance->headers['Content-Type']        = 'application/octet-stream';
        $instance->headers['Content-Disposition'] = 'attachment; filename="' . addslashes($fileName) . '"';
        $instance->headers['Content-Length']      = (string)filesize($filePath);
        $instance->headers['X-Sendfile']          = $filePath; // 标记为文件下载模式

        return $instance;
    }

    // ─────────────────── HTTP 错误快捷方法 ───────────────────

    public static function notFound(string $message = '404 Not Found'): self
    {
        return new self($message, 404);
    }

    public static function forbidden(string $message = '403 Forbidden'): self
    {
        return new self($message, 403);
    }

    public static function unauthorized(string $message = '401 Unauthorized'): self
    {
        return new self($message, 401);
    }

    public static function serverError(string $message = '500 Internal Server Error'): self
    {
        return new self($message, 500);
    }

    public static function badRequest(string $message = '400 Bad Request'): self
    {
        return new self($message, 400);
    }

    // ─────────────────── 链式方法 ───────────────────

    /**
     * 追加 Flash 数据（用于 redirect 链式调用）
     *
     * @param string $key   键名（如 'message', 'error', 'success'）
     * @param mixed  $value 值
     * @example Response::redirect('/login')->with('error', '用户名或密码错误')
     * @example Response::redirect('/login')->with('success', '登录成功')
     */
    public function with(string $key, $value): self
    {
        session()->flash($key, $value);
        return $this;
    }

    /**
     * 重定向时闪存当前请求的输入数据（如表单提交的值）
     */
    public function withInput(): self
    {
        if ($this->isRedirect() && !empty($_POST)) {
            session()->flashInput($_POST);
        }
        return $this;
    }

    /**
     * 重定向时闪存验证错误
     *
     * @param array<string, string> $errors ['字段名' => '错误信息']
     */
    public function withErrors(array $errors): self
    {
        if ($this->isRedirect() && !empty($errors)) {
            session()->flash('_validation_errors', $errors);
        }
        return $this;
    }

    // ─────────────────── 内容 / 头操作 ───────────────────

    public function setContent(string $content): self
    {
        $this->content = $content;
        return $this;
    }

    public function setStatusCode(int $code): self
    {
        $this->statusCode = $code;
        return $this;
    }

    public function setHeader(string $name, string $value): self
    {
        $this->headers[$name] = $value;
        return $this;
    }

    public function setHeaders(array $headers): self
    {
        foreach ($headers as $name => $value) {
            $this->headers[$name] = $value;
        }
        return $this;
    }

    public function removeHeader(string $name): self
    {
        unset($this->headers[$name]);
        return $this;
    }

    public function getHeader(string $name): ?string
    {
        return $this->headers[$name] ?? null;
    }

    public function getHeaders(): array
    {
        return $this->headers;
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    // ─────────────────── 判断方法 ───────────────────

    public function isRedirect(): bool
    {
        return !empty($this->headers['Location']);
    }

    public function isSuccessful(): bool
    {
        return $this->statusCode >= 200 && $this->statusCode < 300;
    }

    public function isClientError(): bool
    {
        return $this->statusCode >= 400 && $this->statusCode < 500;
    }

    public function isServerError(): bool
    {
        return $this->statusCode >= 500 && $this->statusCode < 600;
    }

    /**
     * 发送前是否已发送
     */
    public function isSent(): bool
    {
        return $this->sent;
    }

    // ─────────────────── 发送与析构 ───────────────────

    /**
     * 发送 HTTP 响应（仅发送一次）
     *
     * - 对于重定向：发送状态码和 Location 头，不输出 body
     * - 对于文件下载：发送头后读取文件
     * - 对于普通响应：发送状态码、头、内容
     *
     * 注意：send() 完成后脚本会继续执行后续代码。
     *       重定向场景中，析构函数会额外调用 exit 以确保脚本终止。
     */
    public function send(): self
    {
        if ($this->sent) {
            return $this;
        }

        // 状态码
        http_response_code($this->statusCode);

        // 文件下载 — 使用 X-Sendfile 优化
        if (!empty($this->headers['X-Sendfile'])) {
            $filePath = $this->headers['X-Sendfile'];
            unset($this->headers['X-Sendfile']);

            foreach ($this->headers as $name => $value) {
                header("{$name}: {$value}");
            }

            readfile($filePath);
            $this->sent = true;
            return $this;
        }

        // 常规头
        foreach ($this->headers as $name => $value) {
            header("{$name}: {$value}");
        }

        // 重定向：不输出 body
        if (!$this->isRedirect() && $this->content !== null) {
            echo $this->content;
        }

        $this->sent = true;
        return $this;
    }

    /**
     * 发送 JSON 响应（实例方法，立即发送并退出）
     */
    public function withJson(): self
    {
        $this->headers['Content-Type'] = 'application/json; charset=utf-8';
        $this->send();
        exit;
    }

    /**
     * 析构时自动发送未发送的响应
     *
     * - 重定向响应：发送后自动 exit，确保脚本不会继续执行
     * - 普通响应（渲染视图等）：仅发送，不 exit，由框架控制流管理
     * - 可在 CLI / 测试中通过 disableAutoSend() 关闭
     */
    public function __destruct()
    {
        if (!$this->sent && static::$autoSendEnabled) {
            $this->send();
            if ($this->isRedirect()) {
                exit;
            }
        }
    }

    /**
     * 关闭析构自动发送（CLI / 测试场景）
     */
    public static function disableAutoSend(): void
    {
        static::$autoSendEnabled = false;
    }

    /**
     * 启用析构自动发送
     */
    public static function enableAutoSend(): void
    {
        static::$autoSendEnabled = true;
    }

    /**
     * 字符串化（调试用）
     */
    public function __toString(): string
    {
        return sprintf(
            'HTTP %d %s | %s',
            $this->statusCode,
            $this->isRedirect() ? '→ ' . $this->headers['Location'] : '',
            $this->content ? substr($this->content, 0, 120) : '(empty)'
        );
    }
}

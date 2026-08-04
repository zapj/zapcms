<?php

namespace zap\http;

class ZapRequest
{
    /** @var self 单例 */
    protected static ?ZapRequest $instance = null;

    /** @var array 请求头缓存 */
    protected static ?array $headers = null;

    /** @var string ISO-639 语言代码 */
    protected static ?string $language = null;

    /** @var array|null 解析后的 JSON 请求体缓存 */
    protected ?array $jsonBody = null;

    /** @var bool 是否已尝试解析 JSON */
    protected bool $jsonParsed = false;

    public static function instance(): self
    {
        if (static::$instance === null) {
            static::$instance = new self();
        }
        return static::$instance;
    }

    // ───────────────────── 请求方法 ─────────────────────

    public function isMethod(string $method): bool
    {
        return strtoupper($this->method()) === strtoupper($method);
    }

    public function isPost(): bool
    {
        return $this->isMethod('POST');
    }

    public function isGet(): bool
    {
        return $this->isMethod('GET');
    }

    public function isPut(): bool
    {
        return $this->isMethod('PUT');
    }

    public function isPatch(): bool
    {
        return $this->isMethod('PATCH');
    }

    public function isDelete(): bool
    {
        return $this->isMethod('DELETE');
    }

    public function isOptions(): bool
    {
        return $this->isMethod('OPTIONS');
    }

    public function isHead(): bool
    {
        return $this->isMethod('HEAD');
    }

    public function isAjax(): bool
    {
        return strtolower($this->headers('X-Requested-With', '')) === 'xmlhttprequest';
    }

    public function isJson(): bool
    {
        $contentType = $this->headers('Content-Type', '');
        return strpos(strtolower($contentType), 'application/json') !== false;
    }

    public function method(): string
    {
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

        // 支持 X-HTTP-Method-Override
        if ($method === 'POST') {
            $override = $this->headers('X-HTTP-Method-Override', $this->post('_method'));
            if ($override) {
                $method = strtoupper($override);
            }
        }

        return $method;
    }

    // ───────────────────── URL ─────────────────────

    public function url(): string
    {
        return $this->scheme() . '://' . $this->host() . $this->uri();
    }

    public function fullUrl(): string
    {
        return $this->url() . ($_SERVER['QUERY_STRING'] ?? '' ? '?' . $_SERVER['QUERY_STRING'] : '');
    }

    public function uri(): string
    {
        return $_SERVER['REQUEST_URI'] ?? '/';
    }

    public function path(): string
    {
        $uri = $this->uri();
        return parse_url($uri, PHP_URL_PATH) ?: '/';
    }

    public function scheme(): string
    {
        if (
            (!empty($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) !== 'off')
            || (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https')
        ) {
            return 'https';
        }
        return 'http';
    }

    public function host(): string
    {
        return $_SERVER['HTTP_HOST'] ?? $_SERVER['SERVER_NAME'] ?? 'localhost';
    }

    public function port(): int
    {
        return (int)($_SERVER['SERVER_PORT'] ?? 80);
    }

    public function query($key = null, $default = null)
    {
        if ($key === null) {
            return $_GET;
        }
        return $_GET[$key] ?? $default;
    }

    /**
     * Alias for query() - get GET parameter
     */
    public function get($key = null, $default = null)
    {
        return $this->query($key, $default);
    }

    // ───────────────────── IP & Agent ─────────────────────

    public function ip(): string
    {
        $headers = [
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_FORWARDED',
            'HTTP_X_CLUSTER_CLIENT_IP',
            'HTTP_FORWARDED_FOR',
            'HTTP_FORWARDED',
            'HTTP_CLIENT_IP',
            'REMOTE_ADDR',
        ];

        foreach ($headers as $header) {
            if (!empty($_SERVER[$header])) {
                $ips = explode(',', $_SERVER[$header]);
                $ip = trim($ips[0]);
                if (filter_var($ip, FILTER_VALIDATE_IP)) {
                    return $ip;
                }
            }
        }

        return '0.0.0.0';
    }

    public function userAgent(): string
    {
        return $_SERVER['HTTP_USER_AGENT'] ?? '';
    }

    public function referer(): string
    {
        return $_SERVER['HTTP_REFERER'] ?? '';
    }

    // ───────────────────── 输入数据 ─────────────────────

    /**
     * 获取 POST 数据
     *
     * @param string|null $key     键名
     * @param mixed       $default 默认值
     * @return mixed
     */
    public function post($key = null, $default = null)
    {
        if ($key === null) {
            return $_POST;
        }
        return $_POST[$key] ?? $default;
    }

    /**
     * 通用输入获取（GET > POST > JSON > 默认值）
     *
     * @param string|null $key     键名
     * @param mixed       $default 默认值
     * @return mixed
     */
    public function input($key = null, $default = null)
    {
        // 优先 GET
        if (isset($_GET[$key])) {
            return $_GET[$key];
        }

        // 其次 POST
        if (isset($_POST[$key])) {
            return $_POST[$key];
        }

        // 最后 JSON 请求体
        $json = $this->json();
        if ($json !== null && array_key_exists($key, $json)) {
            return $json[$key];
        }

        return $default;
    }

    /**
     * 检查输入是否存在
     *
     * @return bool
     */
    public function has(string $key): bool
    {
        if (isset($_GET[$key]) || isset($_POST[$key])) {
            return true;
        }
        $json = $this->json();
        return $json !== null && array_key_exists($key, $json);
    }

    /**
     * 仅获取指定键的输入
     */
    public function only(array $keys): array
    {
        $result = [];
        foreach ($keys as $key) {
            $result[$key] = $this->input($key);
        }
        return $result;
    }

    /**
     * 排除指定键的输入
     */
    public function except(array $keys): array
    {
        $all = array_merge($this->json() ?? [], $_POST, $_GET);
        return array_diff_key($all, array_flip($keys));
    }

    /**
     * 获取所有输入
     */
    public function all(): array
    {
        return array_merge($this->json() ?? [], $_POST, $_GET);
    }

    /**
     * 获取 JSON 请求体（自动解析）
     *
     * @return array|null
     */
    public function json(): ?array
    {
        if ($this->jsonParsed) {
            return $this->jsonBody;
        }

        $this->jsonParsed = true;

        if (!$this->isJson()) {
            return null;
        }

        $raw = file_get_contents('php://input');
        if (empty($raw)) {
            return null;
        }

        $data = json_decode($raw, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return null;
        }

        $this->jsonBody = (array)$data;
        return $this->jsonBody;
    }

    /**
     * 获取原始请求体
     */
    public function rawBody(): string
    {
        return file_get_contents('php://input') ?: '';
    }

    // ───────────────────── 请求头 ─────────────────────

    /**
     * 获取请求头
     *
     * @param string|null $key     头名称（或 null 返回全部）
     * @param mixed       $default 默认值
     * @return mixed
     */
    public function headers($key = null, $default = null)
    {
        if (static::$headers === null) {
            if (function_exists('getallheaders')) {
                $headers = getallheaders();
                if ($headers !== false) {
                    static::$headers = $headers;
                    // 将键统一为小写便于查找
                    static::$headers = array_change_key_case(static::$headers, CASE_LOWER);
                }
            }

            if (static::$headers === null) {
                static::$headers = [];
                foreach ($_SERVER as $name => $value) {
                    if (strpos($name, 'HTTP_') === 0) {
                        $headerName = str_replace('_', '-', substr($name, 5));
                        static::$headers[strtolower($headerName)] = $value;
                    }
                }
            }
        }

        if ($key === null) {
            return static::$headers;
        }

        return static::$headers[strtolower($key)] ?? $default;
    }

    // ───────────────────── 文件上传 ─────────────────────

    /**
     * 获取上传文件
     *
     * @param string|null $key 文件字段名，null 返回全部
     * @return array|null
     */
    public function file($key = null)
    {
        if ($key === null) {
            return $_FILES;
        }
        return $_FILES[$key] ?? null;
    }

    /**
     * 检查是否有上传文件
     */
    public function hasFile(string $key): bool
    {
        return isset($_FILES[$key]) && $_FILES[$key]['error'] !== UPLOAD_ERR_NO_FILE;
    }

    // ───────────────────── 语言 ─────────────────────

    /**
     * 获取客户端首选语言
     */
    public function language(): string
    {
        if (static::$language !== null) {
            return static::$language;
        }

        if (!isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
            static::$language = 'en';
            return static::$language;
        }

        // 解析 Accept-Language
        $langs = [];
        foreach (explode(',', $_SERVER['HTTP_ACCEPT_LANGUAGE']) as $part) {
            if (preg_match('/([a-z]{2}(?:-[A-Z]{2})?)\s*(?:;\s*q=([0-9.]+))?/', trim($part), $matches)) {
                $langs[$matches[1]] = (float)($matches[2] ?? 1.0);
            }
        }

        arsort($langs);
        static::$language = (string)array_key_first($langs);
        return static::$language;
    }
}

<?php

namespace zap\http;

use zap\util\Arr;

class ZapRequest
{
    protected $method;

    protected static $instance;

    public static function instance(): ZapRequest
    {
        if(is_null(static::$instance)){
            static::$instance = new ZapRequest();
            static::$instance->init();
        }
        return static::$instance;
    }

    /**
     * Get the public ip address of the user.
     *
     * @param string $default
     * @return  array|string
     */
    public function ip(string $default = '') {
        $clientIP = $default;
        if (isset($_SERVER['HTTP_CLIENT_IP'])) {
            $clientIP = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (isset($_SERVER['HTTP_CF_CONNECTING_IP'])) {
            $clientIP = $_SERVER['HTTP_CF_CONNECTING_IP'];
        } elseif (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $clientIP = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } elseif (isset($_SERVER['HTTP_X_FORWARDED'])) {
            $clientIP = $_SERVER['HTTP_X_FORWARDED'];
        } elseif (isset($_SERVER['HTTP_FORWARDED_FOR'])) {
            $clientIP = $_SERVER['HTTP_FORWARDED_FOR'];
        } elseif (isset($_SERVER['HTTP_FORWARDED'])) {
            $clientIP = $_SERVER['HTTP_FORWARDED'];
        } elseif (isset($_SERVER['REMOTE_ADDR'])) {
            $clientIP = $_SERVER['REMOTE_ADDR'];
        }

        return $clientIP;
    }

    /**
     * 获取真实IP地址
     * @param string $default
     * @param bool $exclude_reserved
     *
     * @return false|mixed|string
     */
    public function realIp(string $default = '', bool $exclude_reserved = false) {
        static $server_keys = null;
        if (empty($server_keys)) {
            $server_keys = array('HTTP_CLIENT_IP', 'REMOTE_ADDR', 'HTTP_X_CLUSTER_CLIENT_IP', 'HTTP_X_FORWARDED_FOR');
        }
        foreach ($server_keys as $key) {
            if (!static::server($key)) {
                continue;
            }
            $ips = explode(',', static::server($key));
            array_walk($ips, function (&$ip) {
                $ip = trim($ip);
            });
            $ips = array_filter($ips,
                function ($ip) use ($exclude_reserved) {
                    return filter_var($ip, FILTER_VALIDATE_IP, $exclude_reserved ? FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE : null);
                });
            if ($ips) {
                return reset($ips);
            }
        }
        return $default;
    }

    /**
     * Request Get
     * @param string|null $name
     * @param null|mixed $default
     * @return mixed
     */
    public function get(string $name = null, $default = null) {
        if ($name == null && $default == null) {
            return $_GET;
        }
        return Arr::get($_GET, $name, $default);
    }

    /**
     * Request Post
     * @param string|null $name
     * @param null|mixed $default
     * @return mixed
     */
    public function post(string $name = null, $default = null) {
        if ($name == null && $default == null) {
            return $_POST;
        }
        return Arr::get($_POST, $name, $default);
    }

    /**
     * Request All
     * @param string|null $name
     * @param null|mixed $default
     * @return mixed
     */
    public function all(string $name = null, $default = null) {
        if ($name == null && $default == null) {
            return $_REQUEST;
        }
        return Arr::get($_REQUEST, $name, $default);
    }

    public function protocol(): string
    {
        if (static::server('HTTPS') == 'on' or
            static::server('HTTPS') == 1 or
            static::server('SERVER_PORT') == 443 or
            static::server('HTTP_X_FORWARDED_PROTO') == 'https' or
            static::server('HTTP_X_FORWARDED_PORT') == 443) {
            return 'https';
        }
        return 'http';
    }


    public function isAjax(): bool
    {
        return ($this->server('HTTP_X_REQUESTED_WITH') !== null) and strtolower($this->server('HTTP_X_REQUESTED_WITH')) === 'xmlhttprequest';
    }

    public function isJson(): bool
    {
        $content_type = $_SERVER['CONTENT_TYPE'] ?? '';
        return (stripos($content_type, 'application/json') !== false);
    }

    public function isXml(): bool
    {
        $content_type = $_SERVER['CONTENT_TYPE'] ?? '';
        return (stripos($content_type, 'application/xml') !== false);
    }

    public function prevUrl($default = '') {
        return $this->server('HTTP_REFERER', $default);
    }

    public function userAgent($default = '') {
        return $this->server('HTTP_USER_AGENT', $default);
    }

    public function file($key = null, $default = null) {
        return is_null($key) ? $_FILES : Arr::get($_FILES, $key, $default);
    }

    /**
     * @param $key
     * @param $default
     * @return array|mixed|null
     */
    public function cookie($key = null, $default = null) {
        return (func_num_args() === 0) ? $_COOKIE : Arr::get($_COOKIE, $key, $default);
    }

    public function server($key = null, $default = null) {
        return is_null($key) ? $_SERVER : Arr::get($_SERVER, strtoupper($key), $default);
    }

    public function headers($key = null, $default = null) {
        static $headers = null;
        if ($headers === null) {
            if (function_exists('getallheaders')) {
                $headers = getallheaders();

                if ($headers !== false) {
                    return $headers;
                }
            }

            foreach ($_SERVER as $name => $value) {
                if ((substr($name, 0, 5) == 'HTTP_') || ($name == 'CONTENT_TYPE') || ($name == 'CONTENT_LENGTH')) {
                    $headers[str_replace(array(' ', 'Http'), array('-', 'HTTP'), ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
                }
            }
        }
        return empty($headers) ? $default : (is_null($key) ? $headers : Arr::get($headers, $key, $default));
    }


    public function query_string($default = '') {
        return $this->server('QUERY_STRING', $default);
    }

    /**
     * HTTP isMethod
     * @param string $method
     * @return bool
     */
    public function isMethod(string $method): bool
    {
        return (strtoupper($this->method) == strtoupper($method));
    }

    public function isPost(): bool
    {
        return static::isMethod('post');
    }

    public function isGet(): bool
    {
        return static::isMethod('get');
    }

    /**
     * Get Method
     * @return string
     */
    public function method(): string
    {
        return $this->method;
    }

    public function raw() {
        return file_get_contents('php://input');
    }

    public function getScriptName($suffix = '') {
        if (isset($_SERVER['SCRIPT_FILENAME']) && !empty($_SERVER['SCRIPT_FILENAME'])) {
            return basename($_SERVER['SCRIPT_FILENAME'], $suffix);
        } else if (isset($_SERVER['PHP_SELF']) && !empty($_SERVER['PHP_SELF'])) {
            return basename($_SERVER['PHP_SELF'], $suffix);
        } else if (isset($_SERVER['SCRIPT_NAME']) && !empty($_SERVER['SCRIPT_NAME'])) {
            return basename($_SERVER['SCRIPT_NAME'], $suffix);
        } else if (isset($_SERVER['REQUEST_URI']) && !empty($_SERVER['REQUEST_URI'])) {
            return basename($_SERVER['REQUEST_URI'], $suffix);
        }
        return basename('index.php', $suffix);
    }


    public function init(){
        $this->method = strtoupper($_SERVER['REQUEST_METHOD']);
        if ($this->method == 'POST' && array_key_exists('HTTP_X_HTTP_METHOD', $_SERVER)) {
            $this->method = strtoupper($_SERVER['HTTP_X_HTTP_METHOD']);
            array_key_exists('X-HTTP-Method-Override', $_SERVER) && $this->method = strtoupper($_SERVER['X-HTTP-Method-Override']);
        } else if ($this->method == 'POST' && array_key_exists('X-HTTP-Method-Override', $_SERVER)) {
            $this->method = strtoupper($_SERVER['X-HTTP-Method-Override']);
        } else if ($_SERVER['REQUEST_METHOD'] == 'POST' && array_key_exists('_method', $_POST)) {
            $this->method = strtoupper($_POST['_method']);
        }
    }

    public function getPreferredLanguage() {
        $default = config('config.fallback_locale','zh-CN');
        $available_languages = config('config.available_languages',[]);
        $acceptLanguages = preg_split('#[,;]#',$_SERVER["HTTP_ACCEPT_LANGUAGE"]);
        foreach($acceptLanguages as $language) {
            if(array_search($language,$available_languages)){
                return $language;
            }
        }
        return $default;
    }
}
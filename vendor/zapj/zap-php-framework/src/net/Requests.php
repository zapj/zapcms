<?php

namespace zap\net;

use zap\exception\CurlException;

class Requests
{
    const GET     = 'GET';
    const POST    = 'POST';
    const PUT     = 'PUT';
    const PATCH   = 'PATCH';
    const DELETE  = 'DELETE';
    const HEAD    = 'HEAD';
    const OPTIONS = 'OPTIONS';
    const TRACE   = 'TRACE';
    const VERSION = '1.0.0';

    protected static string $caCert = ZAP_SRC . '/resources/certificates/cacert.pem';
    protected static string $userAgent = 'Zap-PHP/' . self::VERSION;
    protected static int $defaultTimeout = 30;
    protected static int $defaultConnectTimeout = 10;
    protected static bool $defaultFollowRedirects = true;
    protected static int $maxRedirects = 5;

    private function __construct() {}

    // ===================== 配置 =====================

    public static function setUserAgent(string $ua): void
    {
        self::$userAgent = $ua;
    }

    public static function setCaCert(string $path): void
    {
        self::$caCert = $path;
    }

    public static function getCaCert(): string
    {
        return self::$caCert;
    }

    public static function setDefaultTimeout(int $seconds): void
    {
        self::$defaultTimeout = $seconds;
    }

    public static function setDefaultConnectTimeout(int $seconds): void
    {
        self::$defaultConnectTimeout = $seconds;
    }

    // ===================== HTTP 方法 =====================

    public static function get($url, $params = [], $headers = [], $options = [])
    {
        return self::request('GET', $url, $params, $headers, $options);
    }

    public static function post($url, $data = [], $headers = [], $options = [])
    {
        return self::request('POST', $url, $data, $headers, $options);
    }

    public static function put($url, $data = [], $headers = [], $options = [])
    {
        return self::request('PUT', $url, $data, $headers, $options);
    }

    public static function patch($url, $data = [], $headers = [], $options = [])
    {
        return self::request('PATCH', $url, $data, $headers, $options);
    }

    public static function delete($url, $params = [], $headers = [], $options = [])
    {
        return self::request('DELETE', $url, $params, $headers, $options);
    }

    public static function head($url, $headers = [], $options = [])
    {
        return self::request('HEAD', $url, [], $headers, $options);
    }

    public static function options($url, $data = [], $headers = [], $options = [])
    {
        return self::request('OPTIONS', $url, $data, $headers, $options);
    }

    // ===================== 快捷方法 =====================

    /**
     * 发送 JSON 请求
     */
    public static function json($method, $url, $data = [], $headers = [], $options = [])
    {
        $headers = array_merge($headers, ['Content-Type: application/json']);
        $body = is_string($data) ? $data : json_encode($data, JSON_UNESCAPED_UNICODE);
        return self::request(strtoupper($method), $url, $body, $headers, $options);
    }

    /**
     * 快捷 GET JSON
     */
    public static function getJson($url, $params = [], $headers = [], $options = [])
    {
        $response = self::get($url, $params, $headers, $options);
        return $response->json();
    }

    /**
     * 快捷 POST JSON
     */
    public static function postJson($url, $data = [], $headers = [], $options = [])
    {
        return self::json('POST', $url, $data, $headers, $options);
    }

    /**
     * 发送表单上传请求
     */
    public static function multipart($url, $fields = [], $files = [], $headers = [], $options = [])
    {
        $postFields = [];
        foreach ($fields as $key => $value) {
            $postFields[$key] = $value;
        }
        foreach ($files as $key => $path) {
            $postFields[$key] = new \CURLFile($path);
        }
        $headers = array_diff_key($headers, array_flip(preg_grep('/^Content-Type:/i', $headers)));
        return self::request('POST', $url, $postFields, $headers, $options);
    }

    // ===================== 核心请求方法 =====================

    public static function request($method, $url, $params = [], $headers = [], $options = [])
    {
        $method = strtoupper($method);
        $options = array_merge([
            'timeout'          => self::$defaultTimeout,
            'connect_timeout'  => self::$defaultConnectTimeout,
            'ssl_verify'       => true,
            'return_header'    => false,
            'follow_redirects' => self::$defaultFollowRedirects,
            'max_redirects'    => self::$maxRedirects,
            'cookie'           => null,
            'cookie_file'      => null,
            'auth'             => null,
            'referer'          => null,
        ], $options);

        $ch = curl_init();

        // URL 和查询参数
        if (in_array($method, ['GET', 'DELETE', 'HEAD']) && !empty($params)) {
            $url .= (str_contains($url, '?') ? '&' : '?') . http_build_query($params);
        }

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, $options['return_header']);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_USERAGENT, self::$userAgent);

        // 超时
        curl_setopt($ch, CURLOPT_TIMEOUT, $options['timeout']);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $options['connect_timeout']);

        // SSL
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, $options['ssl_verify']);
        if ($options['ssl_verify'] && is_file(self::$caCert)) {
            curl_setopt($ch, CURLOPT_CAINFO, self::$caCert);
        }

        // 重定向
        if ($options['follow_redirects']) {
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_MAXREDIRS, $options['max_redirects']);
        }

        // Cookie
        if ($options['cookie']) {
            curl_setopt($ch, CURLOPT_COOKIE, $options['cookie']);
        }
        if ($options['cookie_file']) {
            curl_setopt($ch, CURLOPT_COOKIEFILE, $options['cookie_file']);
            curl_setopt($ch, CURLOPT_COOKIEJAR, $options['cookie_file']);
        }

        // HTTP 基本认证
        if ($options['auth'] && is_array($options['auth']) && count($options['auth']) === 2) {
            curl_setopt($ch, CURLOPT_USERPWD, $options['auth'][0] . ':' . $options['auth'][1]);
        }

        // Referer
        if ($options['referer']) {
            curl_setopt($ch, CURLOPT_REFERER, $options['referer']);
        }

        // 根据方法设置请求体和自定义方法
        if (in_array($method, ['POST', 'PUT', 'PATCH', 'OPTIONS'])) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, is_string($params) ? $params : http_build_query($params));
            if ($method === 'POST') {
                curl_setopt($ch, CURLOPT_POST, true);
            } else {
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
            }
        } elseif (!in_array($method, ['GET', 'HEAD'])) {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        }

        // 执行
        $body = curl_exec($ch);
        $error = curl_error($ch);
        $errno = curl_errno($ch);
        $info  = curl_getinfo($ch);

        curl_close($ch);

        if ($error) {
            throw new CurlException('cURL Error: ' . $error, $errno);
        }

        return new Response($body, $info);
    }

    // ===================== 并发请求 =====================

    /**
     * 并发发送多个请求
     *
     * @param array $requests  [['method' => 'GET', 'url' => '...', 'params' => [], 'headers' => []], ...]
     * @return Response[]
     * @throws CurlException
     */
    public static function multi(array $requests): array
    {
        $multiHandle = curl_multi_init();
        $handles = [];
        $results = [];

        foreach ($requests as $i => $req) {
            $method = strtoupper($req['method'] ?? 'GET');
            $url    = $req['url'];
            $params = $req['params'] ?? [];
            $headers = $req['headers'] ?? [];
            $options = $req['options'] ?? [];

            // 用单次请求的配置构建 curl handle
            $ch = self::buildHandle($method, $url, $params, $headers, $options);
            curl_multi_add_handle($multiHandle, $ch);
            $handles[$i] = $ch;
        }

        // 执行所有请求
        do {
            $status = curl_multi_exec($multiHandle, $running);
            if ($running) {
                curl_multi_select($multiHandle);
            }
        } while ($running && $status === CURLM_OK);

        // 收集结果
        foreach ($handles as $i => $ch) {
            $body = curl_multi_getcontent($ch);
            $error = curl_error($ch);
            $errno = curl_errno($ch);
            $info  = curl_getinfo($ch);

            curl_multi_remove_handle($multiHandle, $ch);

            if ($error) {
                $results[$i] = new CurlException('cURL Error: ' . $error, $errno);
            } else {
                $results[$i] = new Response($body, $info);
            }
        }

        curl_multi_close($multiHandle);
        return $results;
    }

    /**
     * 构建 curl handle（用于 multi 和内部复用）
     */
    protected static function buildHandle($method, $url, $params = [], $headers = [], $options = [])
    {
        $method = strtoupper($method);
        $options = array_merge([
            'timeout'          => self::$defaultTimeout,
            'connect_timeout'  => self::$defaultConnectTimeout,
            'ssl_verify'       => true,
            'return_header'    => false,
            'follow_redirects' => self::$defaultFollowRedirects,
            'max_redirects'    => self::$maxRedirects,
            'cookie'           => null,
            'auth'             => null,
        ], $options);

        $ch = curl_init();

        if (in_array($method, ['GET', 'DELETE', 'HEAD']) && !empty($params)) {
            $url .= (str_contains($url, '?') ? '&' : '?') . http_build_query($params);
        }

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, $options['return_header']);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_USERAGENT, self::$userAgent);
        curl_setopt($ch, CURLOPT_TIMEOUT, $options['timeout']);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $options['connect_timeout']);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, $options['ssl_verify']);

        if ($options['ssl_verify'] && is_file(self::$caCert)) {
            curl_setopt($ch, CURLOPT_CAINFO, self::$caCert);
        }

        if ($options['follow_redirects']) {
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_MAXREDIRS, $options['max_redirects']);
        }

        if ($options['cookie']) {
            curl_setopt($ch, CURLOPT_COOKIE, $options['cookie']);
        }

        if ($options['auth'] && is_array($options['auth']) && count($options['auth']) === 2) {
            curl_setopt($ch, CURLOPT_USERPWD, $options['auth'][0] . ':' . $options['auth'][1]);
        }

        if (in_array($method, ['POST', 'PUT', 'PATCH', 'OPTIONS'])) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, is_string($params) ? $params : http_build_query($params));
            if ($method === 'POST') {
                curl_setopt($ch, CURLOPT_POST, true);
            } else {
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
            }
        } elseif (!in_array($method, ['GET', 'HEAD'])) {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        }

        return $ch;
    }
}

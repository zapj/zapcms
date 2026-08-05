<?php

namespace zap\net;

use zap\exception\CurlException;

/**
 * 精简 HTTP 客户端（向后兼容）
 *
 * 内部复用 Requests，保持原有的 get/post 签名。
 */
class Curl extends Requests
{
    /**
     * GET 请求（兼容旧签名）
     *
     * @param string       $url
     * @param string|array $params  查询参数，非数组时直接拼接 URL
     * @param array        $headers
     * @return string
     * @throws CurlException
     */
    public static function get($url, $params = [], $headers = [])
    {
        $response = parent::get($url, is_array($params) ? $params : [], $headers);
        return $response->body();
    }

    /**
     * POST 请求（兼容旧签名）
     *
     * @param string            $url
     * @param string|array      $params  POST 数据；字符串时作为原始 body
     * @param array             $headers
     * @return string
     * @throws CurlException
     */
    public static function post(string $url, array $params = [], array $headers = [])
    {
        $response = parent::post($url, $params, $headers);
        return $response->body();
    }
}

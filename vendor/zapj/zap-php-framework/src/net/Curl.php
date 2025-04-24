<?php

namespace zap\net;

use zap\exception\CurlException;

class Curl {

    /**
     * @param string $url
     * @param $params
     * @param $headers
     * @return bool|string
     * @throws CurlException
     */
    public static function get($url, $params = [], $headers = []) {
        $ch = curl_init();
        if (is_array($params)) {
            curl_setopt($ch, CURLOPT_URL, $url . '?' . http_build_query($params));
        } else {
            curl_setopt($ch, CURLOPT_URL, $url);
        }
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Macintosh; U; PPC Mac OS X; en) AppleWebKit/48 (like Gecko) Safari/48");

        $output = curl_exec($ch);
        $error = curl_error($ch);
        if ($error) {
            throw new CurlException('Curl Error:', $error, curl_errno($ch));
        }
        curl_close($ch);
        return $output;
    }

    /**
     * @param string $url 请求URL
     * @param array $params
     * @param array $headers
     * @return bool|string
     * @throws CurlException
     */
    public static function post(string $url, array $params = [], array $headers = []) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POSTFIELDS, is_string($params) ? $params : http_build_query($params));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_CAINFO, ZAP_SRC . '/resources/certificates/cacert.pem');
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Macintosh; U; PPC Mac OS X; en) AppleWebKit/48 (like Gecko) Safari/48");

        $output = curl_exec($ch);
        $error = curl_error($ch);
        if ($error) {
            throw new CurlException('Curl Error:', $error, curl_errno($ch));
        }
        curl_close($ch);
        return $output;
    }
}
<?php

namespace zap\net;

use zap\exception\CurlException;

class Curl {
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

    public static function post($url, $params = [], $headers = []) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POSTFIELDS, is_string($params) ? $params : http_build_query($params));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
//        curl_setopt($ch, CURLOPT_CAINFO, GCINC . '/CAcertificates/cacert.pem');
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        //    curl_setopt($ch,CURLOPT_USERAGENT,"Mozilla/5.0 (Macintosh; U; PPC Mac OS X; en) AppleWebKit/48 (like Gecko) Safari/48" );

        $output = curl_exec($ch);
        $error = curl_error($ch);
        if ($error) {
            throw new CurlException('Curl Error:', $error, curl_errno($ch));
        }
        curl_close($ch);
        return $output;
    }
}
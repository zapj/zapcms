<?php

namespace zap\net;

use zap\exception\CurlException;

class Requests {
    const POST = 'POST';

    const PUT = 'PUT';

    const GET = 'GET';

    const HEAD = 'HEAD';

    const DELETE = 'DELETE';

    const OPTIONS = 'OPTIONS';

    const TRACE = 'TRACE';

    const PATCH = 'PATCH';

    const VERSION = '0.0.1';

    protected static string $CA_CERT = ZAP_SRC . '/resources/certificates/cacert.pem';

    private function __construct() {}

    public static function get($url, $headers = [], $options = []) {
        return self::request($url, $headers, null, self::GET, $options);
    }

    public static function head($url, $headers = [], $options = []) {
        return self::request($url, $headers, self::HEAD, self::HEAD, $options);
    }

    public static function delete($url, $headers = [], $options = []) {
        return self::request($url, $headers, self::DELETE, self::DELETE, $options);
    }

    public static function post($url, $headers = [], $data = [], $options = []) {
        return self::request($url, $headers, $data, self::POST, $options);
    }

    public static function put($url, $headers = [], $data = [], $options = []) {
        return self::request($url, $headers, $data, self::PUT, $options);
    }

    public static function options($url, $headers = [], $data = [], $options = []) {
        return self::request($url, $headers, $data, self::OPTIONS, $options);
    }

    public static function patch($url, $headers, $data = [], $options = []) {
        return self::request($url, $headers, $data, self::PATCH, $options);
    }

    public static function request($url, $data = [], $type = self::GET,$headers = [], $options = []) {
        $options = array_merge(['ssl_verify' => true,'headers'=>false], $options);
        $ch = curl_init();
        if($type === self::GET) {
            $url = $url.'?'.http_build_query($data);
        }
        curl_setopt($ch, CURLOPT_URL, $url);
        if($type === self::POST){
            curl_setopt($ch, CURLOPT_POSTFIELDS, is_string($data) ? $data : http_build_query($data));
            curl_setopt($ch, CURLOPT_POST, 1);
        }else if($type !== self::GET){
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $type);
        }

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, isset($options['header']) && $options['header'] === true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, isset($options['ssl_verify']) && $options['ssl_verify'] === true);
        curl_setopt($ch, CURLOPT_CAINFO, ZAP_SRC . '/resources/certificates/cacert.pem');
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Macintosh; U; PPC Mac OS X; en) AppleWebKit/48 (like Gecko) Safari/48");

        $output = curl_exec($ch);
        $error = curl_error($ch);
        if ($error) {
            return new CurlException('Curl Error:', $error, curl_errno($ch));
        }
        curl_close($ch);
        return $output;
    }

    public static function get_ca_cert(): string
    {
        return self::$CA_CERT;
    }


    public static function set_ca_cert_path($path) {
        self::$CA_CERT = $path;
    }

}


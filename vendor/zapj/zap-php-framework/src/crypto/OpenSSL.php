<?php

namespace zap\crypto;

class OpenSSL
{

    protected const DEFAULT_METHOD = 'aes-256-cbc';


    public static function encrypt(string $plain, string $key, $method = null): string
    {
        $method = is_null($method) ? static::DEFAULT_METHOD : $method;
        $ivSize = openssl_cipher_iv_length($method);

        $iv = openssl_random_pseudo_bytes($ivSize);

        return $iv . openssl_encrypt($plain, $method, $key, OPENSSL_RAW_DATA, $iv);
    }


    public static function decrypt(string $cipher, string $key, $method = null): ?string
    {
        $method = is_null($method) ? static::DEFAULT_METHOD : $method;
        $ivSize = openssl_cipher_iv_length($method);

        $iv = mb_substr($cipher, 0, $ivSize, '8bit');
        $cipher = mb_substr($cipher, $ivSize, null, '8bit');

        $value = openssl_decrypt($cipher, $method, $key, OPENSSL_RAW_DATA, $iv);
        if ($value === false) {
            return null;
        }

        return $value;
    }
}
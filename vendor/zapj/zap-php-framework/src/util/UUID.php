<?php

namespace zap\util;

use zap\InvalidArgumentException;

/**
 * Class UUID
 *
 * @see https://github.com/oittaa/uuid-php
 */
class UUID
{
    const NAMESPACE_DNS = '6ba7b810-9dad-11d1-80b4-00c04fd430c8';
    const NAMESPACE_URL = '6ba7b811-9dad-11d1-80b4-00c04fd430c8';
    const NAMESPACE_OID = '6ba7b812-9dad-11d1-80b4-00c04fd430c8';
    const NAMESPACE_X500 = '6ba7b814-9dad-11d1-80b4-00c04fd430c8';
    const NIL = '00000000-0000-0000-0000-000000000000';

    private static function getBytes(string $uuid): string
    {
        if (!self::isValid($uuid)) {
            throw new InvalidArgumentException('Invalid UUID string: ' . $uuid);
        }
        $uhex = str_replace(['urn:', 'uuid:', '-', '{', '}'], '', $uuid);
        $ustr = '';
        for ($i = 0; $i < strlen($uhex); $i += 2) {
            $ustr .= chr(hexdec($uhex[$i] . $uhex[$i + 1]));
        }
        return $ustr;
    }

    private static function uuidFromHash(string $hash, int $version): string
    {
        return sprintf('%08s-%04s-%04x-%04x-%12s',
            substr($hash, 0, 8),
            substr($hash, 8, 4),
            (hexdec(substr($hash, 12, 4)) & 0x0fff) | $version << 12,
            (hexdec(substr($hash, 16, 4)) & 0x3fff) | 0x8000,
            substr($hash, 20, 12)
        );
    }

    /**
     * Generate a version 3 UUID based on the MD5 hash of a namespace identifier and a name.
     */
    public static function uuid3(string $namespace, string $name): string
    {
        $nbytes = self::getBytes($namespace);
        $hash = md5($nbytes . $name);
        return self::uuidFromHash($hash, 3);
    }

    /**
     * Generate a version 4 (random) UUID.
     */
    public static function uuid4(): string
    {
        if (function_exists('random_bytes')) {
            $bytes = random_bytes(16);
        } else {
            $bytes = openssl_random_pseudo_bytes(16, $cryptoStrong);
            if (!$cryptoStrong) {
                // 回退到非加密安全的随机源
                $bytes = '';
                for ($i = 0; $i < 16; $i++) {
                    $bytes .= chr(mt_rand(0, 255));
                }
            }
        }
        return self::uuidFromHash(bin2hex($bytes), 4);
    }

    /**
     * Generate a version 5 UUID based on the SHA-1 hash of a namespace identifier and a name.
     */
    public static function uuid5(string $namespace, string $name): string
    {
        $nbytes = self::getBytes($namespace);
        $hash = sha1($nbytes . $name);
        return self::uuidFromHash($hash, 5);
    }

    /**
     * Check if a string is a valid UUID.
     */
    public static function isValid(string $uuid): bool
    {
        return preg_match(
            '/^(urn:)?(uuid:)?(\{)?[0-9a-f]{8}\-?[0-9a-f]{4}\-?[0-9a-f]{4}\-?[0-9a-f]{4}\-?[0-9a-f]{12}(?(3)\}|)$/i',
            $uuid
        ) === 1;
    }

    /**
     * Check if two UUIDs are equal.
     */
    public static function equals(string $uuid1, string $uuid2): bool
    {
        return self::getBytes($uuid1) === self::getBytes($uuid2);
    }
}

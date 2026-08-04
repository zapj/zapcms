<?php

namespace zap\util;

class Random
{
    const NUM = 0;
    const ALNUM = 1;
    const NUMBERIC = 2;
    const ALPHA = 3;
    const MD5 = 4;
    const SHA1 = 5;
    const UUID = 6;
    const UNIQUE = 7;
    const HEXDEC = 8;
    const NOZERO = 9;
    const DISTINCT = 10;

    /** @var array<int, string> 字符池查找表 */
    private static array $pools = [
        self::ALPHA    => 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ',
        self::ALNUM    => '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ',
        self::NUMBERIC => '0123456789',
        self::NOZERO   => '123456789',
        self::HEXDEC   => '0123456789abcdef',
        self::DISTINCT => '2345679ACDEFHJKLMNPRSTUVWXYZ',
    ];

    public static function int(int $min = 0, ?int $max = null): int
    {
        if ($max === null) {
            $max = mt_getrandmax();
        }
        return random_int($min, $max);
    }

    /**
     * 生成随机字符串（简单版）
     */
    public static function str(int $length = 6): string
    {
        if ($length < 1) {
            return '';
        }
        $pool = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $max = strlen($pool) - 1;
        $result = '';
        for ($i = 0; $i < $length; $i++) {
            $result .= $pool[random_int(0, $max)];
        }
        return $result;
    }

    /**
     * 按类型生成随机字符串
     *
     * @param int $type   类型常量
     * @param int $length 长度
     */
    public static function rand(int $type = self::ALNUM, int $length = 6): string
    {
        switch ($type) {
            case self::NUM:
                return (string) random_int(0, PHP_INT_MAX);

            case self::UNIQUE:
                return md5(uniqid((string) random_int(0, PHP_INT_MAX), true));

            case self::SHA1:
                return sha1(uniqid((string) random_int(0, PHP_INT_MAX), true));

            case self::UUID:
                return UUID::uuid4();

            case self::MD5:
                return md5(uniqid((string) random_int(0, PHP_INT_MAX), true));

            default:
                return self::generateFromPool($type, $length);
        }
    }

    /**
     * 从字符池生成字符串
     */
    private static function generateFromPool(int $type, int $length): string
    {
        $pool = self::$pools[$type] ?? self::$pools[self::ALNUM];
        $max = strlen($pool) - 1;
        $result = '';
        for ($i = 0; $i < $length; $i++) {
            $result .= $pool[random_int(0, $max)];
        }
        return $result;
    }

    /**
     * 获取指定类型的字符池
     */
    public static function pool(int $type): string
    {
        return self::$pools[$type] ?? self::$pools[self::ALNUM];
    }
}

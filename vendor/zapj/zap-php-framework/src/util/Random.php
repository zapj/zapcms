<?php

namespace  zap\util;

class Random {

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

    public static function int($min = 0, $max = RAND_MAX) {
        return mt_rand($min, $max);
    }

    public static function str($length = 6) {
        $pool = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $offset = (62 - $length) - mt_rand(0, 62 - $length);
        return substr(str_shuffle($pool), $offset, $length);
    }

    /**
     * 随机生成
     * @param int $type 类型
     * @param int $length 长度
     */
    public static function rand($type = Random::ALNUM, $length = 6) {
        switch ($type) {
            case Random::NUM:
                return mt_rand();
            case Random::UNIQUE:
                return md5(uniqid(mt_rand()));
            case Random::SHA1 :
                return sha1(uniqid(mt_rand(), true));
            case Random::UUID:
                $pool = array('8', '9', 'a', 'b');
                return sprintf('%s-%s-4%s-%s%s-%s', static::rand('hexdec', 8), static::rand('hexdec', 4), static::rand('hexdec', 3), $pool[array_rand($pool)], static::rand('hexdec', 3), static::rand('hexdec', 12));
            case Random::ALPHA:
            case Random::ALNUM:
            case Random::NUMBERIC:
            case Random::NOZERO:
            case Random::DISTINCT:
            case Random::HEXDEC:
                switch ($type) {
                    case Random::ALPHA:
                        $pool = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
                        break;
                    default:
                    case Random::ALNUM:
                        $pool = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
                        break;
                    case Random::NUMBERIC:
                        $pool = '0123456789';
                        break;
                    case Random::NOZERO:
                        $pool = '123456789';
                        break;
                    case Random::NOZERO:
                        $pool = '2345679ACDEFHJKLMNPRSTUVWXYZ';
                        break;
                    case Random::HEXDEC:
                        $pool = '0123456789abcdef';
                        break;
                }
                $str = '';
                for ($i = 0; $i < $length; $i++) {
                    $str .= substr($pool, mt_rand(0, strlen($pool) - 1), 1);
                }
                return $str;
        }
    }
}
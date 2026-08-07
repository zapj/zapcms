<?php

namespace zap\crypto;

/**
 * 安全随机数生成器
 *
 * 使用 random_bytes / random_int 提供密码学安全的随机值生成。
 */
class Random
{
    // ────────── 原始字节 ──────────

    /**
     * 生成安全的随机字节
     *
     * @param int $length 字节数
     * @throws \Exception 随机源不可用时抛出异常
     */
    public static function bytes(int $length = 32): string
    {
        return random_bytes($length);
    }

    /**
     * 生成安全的随机整数
     *
     * @param int $min 最小值（包含）
     * @param int $max 最大值（包含）
     * @throws \Exception
     */
    public static function int(int $min, int $max): int
    {
        return random_int($min, $max);
    }

    // ────────── 字符串 ──────────

    /**
     * 生成安全的随机十六进制字符串
     *
     * @param int $length 字符串长度（字符数，非字节数）
     */
    public static function hex(int $length = 32): string
    {
        return bin2hex(random_bytes((int) ceil($length / 2)));
    }

    /**
     * 生成安全的随机字母数字字符串
     *
     * @param int $length 字符串长度
     */
    public static function string(int $length = 32): string
    {
        $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
        return self::fromCharset($length, $chars);
    }

    /**
     * 生成 URL 安全的随机 Token（字母数字 + -_）
     *
     * @param int $length 字符串长度
     */
    public static function token(int $length = 48): string
    {
        $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789-_';
        return self::fromCharset($length, $chars);
    }

    /**
     * 生成纯数字随机字符串（如短信验证码）
     *
     * @param int $length 数字位数
     */
    public static function numeric(int $length = 6): string
    {
        return self::fromCharset($length, '0123456789');
    }

    /**
     * 生成易读的随机字符串（排除易混淆字符: 0/O/1/I/l）
     *
     * @param int $length 字符串长度
     */
    public static function readable(int $length = 16): string
    {
        $chars = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghjkmnpqrstuvwxyz23456789';
        return self::fromCharset($length, $chars);
    }

    /**
     * 从自定义字符集生成随机字符串
     *
     * @param int    $length  字符串长度
     * @param string $charset 字符集
     */
    public static function fromCharset(int $length, string $charset): string
    {
        if ($length < 1) {
            return '';
        }

        $charsLen = strlen($charset);
        $result   = '';
        $bytes    = random_bytes($length * 2); // 多取一些保证均匀性

        for ($i = 0; $i < $length; $i++) {
            $result .= $charset[ord($bytes[$i]) % $charsLen];
        }

        return $result;
    }

    /**
     * UUID v4 风格（不依赖 UUID 类时使用）
     */
    public static function uuid(): string
    {
        $bytes = random_bytes(16);

        // 设置版本位 (4) 和变体位 (8/9/a/b)
        $bytes[6] = chr((ord($bytes[6]) & 0x0f) | 0x40);
        $bytes[8] = chr((ord($bytes[8]) & 0x3f) | 0x80);

        return sprintf('%s-%s-%s-%s-%s',
            bin2hex(substr($bytes, 0, 4)),
            bin2hex(substr($bytes, 4, 2)),
            bin2hex(substr($bytes, 6, 2)),
            bin2hex(substr($bytes, 8, 2)),
            bin2hex(substr($bytes, 10, 6))
        );
    }
}

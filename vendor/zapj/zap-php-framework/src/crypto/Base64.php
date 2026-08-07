<?php

namespace zap\crypto;

/**
 * URL 安全的 Base64 编解码
 *
 * 将标准 Base64 中的 +/= 替换为 URL 友好字符，适用场景：URL 参数、Token、Cookie、文件名。
 */
class Base64
{
    /**
     * URL 安全编码
     *
     * 将 + → -，/ → _，去除末尾 =。
     */
    public static function encodeUrlSafe(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    /**
     * URL 安全解码
     *
     * 将 - → +，_ → /，自动补全末尾 =。
     *
     * @return string|false 解码后的原始数据，失败返回 false
     */
    public static function decodeUrlSafe(string $base64): string|false
    {
        $remainder = strlen($base64) % 4;
        if ($remainder !== 0) {
            $base64 .= str_repeat('=', 4 - $remainder);
        }
        return base64_decode(strtr($base64, '-_', '+/'), true);
    }

    /**
     * URL 安全编码（保留填充 =）
     */
    public static function encodeUrlSafePadded(string $data): string
    {
        return strtr(base64_encode($data), '+/', '-_');
    }

    /**
     * 标准解码
     */
    public static function decode(string $data): string|false
    {
        return base64_decode($data, true);
    }

    /**
     * 标准编码
     */
    public static function encode(string $data): string
    {
        return base64_encode($data);
    }
}

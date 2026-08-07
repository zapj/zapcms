<?php

namespace zap\crypto;

/**
 * 哈希与签名工具
 *
 * 提供密码哈希（Argon2id / bcrypt）、HMAC 签名与验证、以及常用快速哈希。
 */
class Hash
{
    // ────────── 密码哈希 ──────────

    /**
     * 生成密码哈希
     *
     * 优先使用 Argon2id，不可用时回退到 bcrypt。
     *
     * @param string $password 明文密码
     * @param array  $options  选项，如 ['cost' => 12]
     */
    public static function password(string $password, array $options = []): string
    {
        if (defined('PASSWORD_ARGON2ID')) {
            $options = array_merge([
                'memory_cost' => 65536,   // 64MB
                'time_cost'   => 4,
                'threads'     => 1,
            ], $options);
            return password_hash($password, PASSWORD_ARGON2ID, $options);
        }

        $options = array_merge(['cost' => 12], $options);
        return password_hash($password, PASSWORD_BCRYPT, $options);
    }

    /**
     * 验证密码
     *
     * @param string $password 待验证的明文密码
     * @param string $hash     已存储的哈希
     */
    public static function passwordVerify(string $password, string $hash): bool
    {
        return password_verify($password, $hash);
    }

    /**
     * 检查哈希是否需要重新计算（算法变更或 cost 调整时）
     *
     * @param string $hash    已存储的哈希
     * @param array  $options 新的哈希选项
     */
    public static function passwordNeedsRehash(string $hash, array $options = []): bool
    {
        if (defined('PASSWORD_ARGON2ID')) {
            $algo    = PASSWORD_ARGON2ID;
            $options = array_merge([
                'memory_cost' => 65536,
                'time_cost'   => 4,
                'threads'     => 1,
            ], $options);
        } else {
            $algo    = PASSWORD_BCRYPT;
            $options = array_merge(['cost' => 12], $options);
        }

        return password_needs_rehash($hash, $algo, $options);
    }

    /**
     * 获取密码哈希的算法信息
     *
     * @return array{algoName: string, options: array}|null
     */
    public static function passwordInfo(string $hash): ?array
    {
        $info = password_get_info($hash);
        if ($info['algo'] === 0) {
            return null;
        }
        return [
            'algoName' => $info['algoName'] ?? 'unknown',
            'options'  => $info['options'] ?? [],
        ];
    }

    // ────────── HMAC ──────────

    /**
     * 生成 HMAC 签名
     *
     * @param string $data      待签名的数据
     * @param string $key       密钥
     * @param string $algorithm 哈希算法（默认 sha256）
     * @param bool   $rawOutput 是否返回原始二进制（默认十六进制）
     */
    public static function hmac(string $data, string $key, string $algorithm = 'sha256', bool $rawOutput = false): string
    {
        return hash_hmac($algorithm, $data, $key, $rawOutput);
    }

    /**
     * 验证 HMAC 签名（constant-time 比较）
     *
     * @param string $data      原始数据
     * @param string $key       密钥
     * @param string $signature 待验证的签名
     * @param string $algorithm 哈希算法
     * @param bool   $isRaw     签名是否为原始二进制
     */
    public static function hmacVerify(string $data, string $key, string $signature, string $algorithm = 'sha256', bool $isRaw = false): bool
    {
        $expected = self::hmac($data, $key, $algorithm, $isRaw);
        return hash_equals($expected, $signature);
    }

    // ────────── 快速哈希 ──────────

    /**
     * SHA-256 哈希
     */
    public static function sha256(string $data, bool $rawOutput = false): string
    {
        return hash('sha256', $data, $rawOutput);
    }

    /**
     * SHA-1 哈希
     */
    public static function sha1(string $data, bool $rawOutput = false): string
    {
        return hash('sha1', $data, $rawOutput);
    }

    /**
     * MD5 哈希
     */
    public static function md5(string $data, bool $rawOutput = false): string
    {
        return hash('md5', $data, $rawOutput);
    }

    /**
     * 文件的 SHA-256 哈希
     */
    public static function sha256File(string $filePath): ?string
    {
        if (!is_file($filePath) || !is_readable($filePath)) {
            return null;
        }
        return hash_file('sha256', $filePath);
    }

    /**
     * 文件的 MD5 哈希
     */
    public static function md5File(string $filePath): ?string
    {
        if (!is_file($filePath) || !is_readable($filePath)) {
            return null;
        }
        return hash_file('md5', $filePath);
    }

    /**
     * 通用的快速哈希（支持任意算法）
     *
     * @param string $algorithm 如 'sha256', 'sha512', 'md5' 等
     */
    public static function make(string $data, string $algorithm = 'sha256', bool $rawOutput = false): string
    {
        return hash($algorithm, $data, $rawOutput);
    }

    /**
     * CRC32 校验（常用于缓存键、去重）
     */
    public static function crc32(string $data): string
    {
        return sprintf('%08x', crc32($data));
    }
}

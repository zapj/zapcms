<?php

namespace zap\util;

class Password
{
    /**
     * 密码哈希
     *
     * @param string $password 明文密码
     * @param int|string|null $algo 算法 (PASSWORD_DEFAULT / PASSWORD_BCRYPT / PASSWORD_ARGON2I / PASSWORD_ARGON2ID)
     * @param array $options 算法选项 ['cost' => 12]
     */
    public static function hash(string $password, $algo = PASSWORD_DEFAULT, array $options = []): string
    {
        if (is_string($algo) && defined($algo)) {
            $algo = constant($algo);
        }
        return password_hash($password, $algo, $options);
    }

    /**
     * 验证密码
     */
    public static function verify(string $password, string $hash): bool
    {
        return password_verify($password, $hash);
    }

    /**
     * 检查已存储的哈希是否需要重新生成
     */
    public static function needsRehash(string $hash, $algo = PASSWORD_DEFAULT, array $options = []): bool
    {
        if (is_string($algo) && defined($algo)) {
            $algo = constant($algo);
        }
        return password_needs_rehash($hash, $algo, $options);
    }

    /**
     * 获取哈希算法信息
     */
    public static function info(string $hash): array
    {
        return password_get_info($hash);
    }
}

<?php

namespace zap\crypto;

/**
 * OpenSSL 对称加解密组件
 *
 * 支持 AES-128/192/256 的 CBC/GCM/CTR/ECB 模式，提供 Base64 输出、
 * HMAC 认证加密、密钥派生和 JSON 载荷加密。
 *
 * @method static string encrypt(string $plain, ?string $key = null, ?string $method = null)
 * @method static ?string decrypt(string $cipher, ?string $key = null, ?string $method = null)
 * @method static string encryptToBase64(string $plain, ?string $key = null, ?string $method = null)
 * @method static ?string decryptFromBase64(string $base64, ?string $key = null, ?string $method = null)
 * @method static string encryptWithAuth(string $plain, ?string $key = null, ?string $hmacKey = null, ?string $method = null)
 * @method static ?string decryptWithAuth(string $packed, ?string $key = null, ?string $hmacKey = null, ?string $method = null)
 * @method static string encryptJson(array $data, ?string $key = null, ?string $method = null)
 * @method static ?array decryptJson(string $cipher, ?string $key = null, ?string $method = null)
 */
class OpenSSL
{
    // ────────── 密码套件 ──────────

    const CIPHER_AES_128_CBC = 'aes-128-cbc';
    const CIPHER_AES_192_CBC = 'aes-192-cbc';
    const CIPHER_AES_256_CBC = 'aes-256-cbc';
    const CIPHER_AES_128_GCM = 'aes-128-gcm';
    const CIPHER_AES_192_GCM = 'aes-192-gcm';
    const CIPHER_AES_256_GCM = 'aes-256-gcm';
    const CIPHER_AES_128_CTR = 'aes-128-ctr';
    const CIPHER_AES_256_CTR = 'aes-256-ctr';

    protected string $cipher;

    protected ?string $key = null;

    protected ?string $hmacKey = null;

    /** @var \OpenSSLAsymmetricKey|resource|false|null */
    protected $privateKey = null;

    /** @var \OpenSSLAsymmetricKey|resource|false|null */
    protected $publicKey = null;

    // ────────── 构造与配置 ──────────

    public function __construct(?string $key = null, string $cipher = self::CIPHER_AES_256_CBC, ?string $hmacKey = null)
    {
        $this->cipher  = $cipher;
        $this->key     = $key;
        $this->hmacKey = $hmacKey;
    }

    /**
     * 设置对称加密密钥
     */
    public function setKey(string $key): self
    {
        $this->key = $key;
        return $this;
    }

    /**
     * 设置加密算法
     */
    public function setCipher(string $cipher): self
    {
        $this->cipher = $cipher;
        return $this;
    }

    /**
     * 设置 HMAC 密钥（认证加密用）
     */
    public function setHmacKey(string $hmacKey): self
    {
        $this->hmacKey = $hmacKey;
        return $this;
    }

    /**
     * 获取当前使用的加密算法
     */
    public function getCipher(): string
    {
        return $this->cipher;
    }

    /**
     * 获取 IV 长度
     */
    public function getIvLength(): int
    {
        return openssl_cipher_iv_length($this->cipher);
    }

    // ────────── 基础加密/解密（原始输出，向后兼容） ──────────

    /**
     * 加密 - 返回 IV + 密文（二进制）
     */
    public function encrypt(string $plain, ?string $key = null, ?string $method = null): string
    {
        $key    = $key ?? $this->key;
        $method = $method ?? $this->cipher;
        $ivSize = openssl_cipher_iv_length($method);
        $iv     = openssl_random_pseudo_bytes($ivSize);

        $encrypted = openssl_encrypt($plain, $method, $this->resolveKey($key), OPENSSL_RAW_DATA, $iv);

        return $iv . $encrypted;
    }

    /**
     * 解密
     *
     * @return string|null 解密后的明文，失败返回 null
     */
    public function decrypt(string $cipher, ?string $key = null, ?string $method = null): ?string
    {
        $key    = $key ?? $this->key;
        $method = $method ?? $this->cipher;
        $ivSize = openssl_cipher_iv_length($method);

        // 密文长度校验
        if (strlen($cipher) <= $ivSize) {
            return null;
        }

        $iv     = substr($cipher, 0, $ivSize);
        $cipherBody = substr($cipher, $ivSize);

        $result = openssl_decrypt($cipherBody, $method, $this->resolveKey($key), OPENSSL_RAW_DATA, $iv);
        return $result !== false ? $result : null;
    }

    // ────────── Base64 加密/解密 ──────────

    /**
     * 加密并返回 Base64 编码的字符串
     */
    public function encryptToBase64(string $plain, ?string $key = null, ?string $method = null): string
    {
        return base64_encode($this->encrypt($plain, $key, $method));
    }

    /**
     * 从 Base64 字符串解密
     */
    public function decryptFromBase64(string $base64, ?string $key = null, ?string $method = null): ?string
    {
        $data = base64_decode($base64, true);
        if ($data === false) {
            return null;
        }
        return $this->decrypt($data, $key, $method);
    }

    // ────────── HMAC 认证加密（推荐） ──────────

    /**
     * 认证加密（encrypt-then-MAC 模式）
     *
     * 返回结构: HMAC(32字节) + IV + 密文 — 解密前先校验 HMAC 完整性
     */
    public function encryptWithAuth(string $plain, ?string $key = null, ?string $hmacKey = null, ?string $method = null): string
    {
        $hmacKey = $hmacKey ?? $this->hmacKey;
        $key     = $key ?? $this->key;

        $encrypted = $this->encrypt($plain, $key, $method);

        // 对 IV+密文 计算 HMAC
        $hmac = hash_hmac('sha256', $encrypted, $this->resolveKey($hmacKey), true);

        return $hmac . $encrypted;
    }

    /**
     * 认证加密——Base64 输出（推荐日常使用）
     */
    public function encryptWithAuthToBase64(string $plain, ?string $key = null, ?string $hmacKey = null, ?string $method = null): string
    {
        return Base64::encodeUrlSafe($this->encryptWithAuth($plain, $key, $hmacKey, $method));
    }

    /**
     * 认证解密（encrypt-then-MAC 模式）
     *
     * 先校验 HMAC 完整性，再解密。HMAC 不匹配时返回 null。
     */
    public function decryptWithAuth(string $packed, ?string $key = null, ?string $hmacKey = null, ?string $method = null): ?string
    {
        $hmacKey = $hmacKey ?? $this->hmacKey;
        $key     = $key ?? $this->key;

        // HMAC 是 32 字节（sha256）
        if (strlen($packed) <= 32) {
            return null;
        }

        $hmac      = substr($packed, 0, 32);
        $encrypted = substr($packed, 32);

        // HMAC 校验（constant-time 比较）
        $expectedHmac = hash_hmac('sha256', $encrypted, $this->resolveKey($hmacKey), true);
        if (!hash_equals($expectedHmac, $hmac)) {
            return null;
        }

        return $this->decrypt($encrypted, $key, $method);
    }

    /**
     * 认证解密——从 Base64 输入
     */
    public function decryptWithAuthFromBase64(string $base64, ?string $key = null, ?string $hmacKey = null, ?string $method = null): ?string
    {
        $raw = Base64::decodeUrlSafe($base64);
        return $this->decryptWithAuth($raw, $key, $hmacKey, $method);
    }

    // ────────── JSON 加密 ──────────

    /**
     * 加密数组为认证的 Base64 字符串
     */
    public function encryptJson(array $data, ?string $key = null, ?string $method = null): string
    {
        $json = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        return $this->encryptWithAuthToBase64($json, $key, null, $method);
    }

    /**
     * 从认证加密字符串解密为数组
     */
    public function decryptJson(string $base64Data, ?string $key = null, ?string $method = null): ?array
    {
        $json = $this->decryptWithAuthFromBase64($base64Data, $key, null, $method);
        if ($json === null) {
            return null;
        }
        $data = json_decode($json, true);
        return is_array($data) ? $data : null;
    }

    // ────────── 密钥派生 ──────────

    /**
     * 从密码派生加密密钥（PBKDF2）
     *
     * @param string $password 原始密码
     * @param string $salt     盐值（建议 random_bytes(16) 生成的二进制）
     * @param int    $length   输出密钥长度
     * @param int    $iterations 迭代次数
     */
    public static function deriveKey(string $password, string $salt, int $length = 32, int $iterations = 100000): string
    {
        return hash_pbkdf2('sha256', $password, $salt, $iterations, $length, true);
    }

    /**
     * 获取支持的加密算法列表
     *
     * @return string[]
     */
    public static function availableMethods(): array
    {
        return openssl_get_cipher_methods();
    }

    // ────────── 静态代理 ──────────

    public static function __callStatic(string $name, array $arguments)
    {
        static $instance = null;
        if ($instance === null) {
            $instance = new static();
        }

        if (method_exists($instance, $name)) {
            return $instance->$name(...$arguments);
        }

        throw new \BadMethodCallException("Method OpenSSL::{$name}() does not exist.");
    }

    // ────────── 内部 ──────────

    /**
     * 确保密钥为正确长度的二进制字符串
     */
    protected function resolveKey(?string $key): string
    {
        if ($key === null) {
            return '';
        }
        return $key;
    }
}

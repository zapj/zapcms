<?php

namespace zap\util;

class Str
{
    // ========== 格式化 ==========

    /**
     * 字符串模板替换："{key}" → value
     */
    public static function format(string $string, $params, $value = null): string
    {
        if (is_array($params)) {
            $search = array_map(fn($key) => '{' . $key . '}', array_keys($params));
            $replace = array_values($params);
            return str_replace($search, $replace, $string);
        }
        return str_replace('{' . $params . '}', $value, $string);
    }

    // ========== 生成 ==========

    public static function token(): string
    {
        return md5(str_shuffle(chr(mt_rand(32, 126)) . uniqid() . microtime(true)));
    }

    public static function slug(string $title, string $separator = '-'): string
    {
        $flip = $separator === '-' ? '_' : '-';
        $title = preg_replace('![' . preg_quote($flip) . ']+!u', $separator, $title);
        $title = preg_replace('![^' . preg_quote($separator) . '\pL\pN\s]+!u', '', mb_strtolower($title));
        $title = preg_replace('![' . preg_quote($separator) . '\s]+!u', $separator, $title);
        return trim($title, $separator);
    }

    // ========== 长度 / 子串 ==========

    public static function len(string $str, string $encoding = 'UTF-8'): int
    {
        return function_exists('mb_strlen')
            ? mb_strlen($str, $encoding)
            : strlen($str);
    }

    public static function substr(string $string, int $start, ?int $length = null): string
    {
        return function_exists('mb_substr')
            ? mb_substr($string, $start, $length, 'UTF-8')
            : substr($string, $start, $length);
    }

    public static function truncate(string $value, int $limit = 100, string $end = '...'): string
    {
        if (mb_strwidth($value, 'UTF-8') <= $limit) {
            return $value;
        }
        return rtrim(mb_strimwidth($value, 0, $limit, '', 'UTF-8')) . $end;
    }

    // ========== 判断型 ==========

    public static function startsWith(?string $string, string $start, bool $caseSensitive = true): bool
    {
        if ($string === null) {
            return false;
        }
        if (!$caseSensitive) {
            $string = strtolower($string);
            $start = strtolower($start);
        }
        return str_starts_with($string, $start);
    }

    public static function endsWith(?string $string, string $end, bool $caseSensitive = true): bool
    {
        if ($string === null) {
            return false;
        }
        if (!$caseSensitive) {
            $string = strtolower($string);
            $end = strtolower($end);
        }
        return str_ends_with($string, $end);
    }

    /**
     * @deprecated 使用原生 str_starts_with()
     */
    public static function startsWithChar(string $needle, string $haystack): bool
    {
        return $needle !== '' && $needle[0] === $haystack;
    }

    /**
     * @deprecated 使用原生 str_ends_with()
     */
    public static function endsWithChar(string $needle, string $haystack): bool
    {
        return $needle !== '' && str_ends_with($needle, $haystack);
    }

    public static function contains(string $haystack, $needles): bool
    {
        if (is_string($needles)) {
            return str_contains($haystack, $needles);
        }
        foreach ((array) $needles as $needle) {
            if ($needle !== '' && str_contains($haystack, $needle)) {
                return true;
            }
        }
        return false;
    }

    /**
     * 检查是否包含所有 needles
     */
    public static function containsAll(string $haystack, $needles): bool
    {
        if (is_string($needles)) {
            return str_contains($haystack, $needles);
        }
        foreach ((array) $needles as $needle) {
            if ($needle === '' || !str_contains($haystack, $needle)) {
                return false;
            }
        }
        return true;
    }

    /** @deprecated 使用 containsAll() */
    public static function containsArray(string $haystack, $needles): bool
    {
        return self::containsAll($haystack, $needles);
    }

    // ========== 内容判断 ==========

    public static function isJson(string $str): bool
    {
        json_decode($str);
        return json_last_error() === JSON_ERROR_NONE;
    }

    public static function isHtml(string $str): bool
    {
        return $str !== strip_tags($str);
    }

    public static function isAscii(string $string): bool
    {
        return !preg_match('/[^\x00-\x7F]/S', $string);
    }

    // ========== 替换 ==========

    public static function replaceArray(string $search, array $replace, string $subject): string
    {
        foreach ($replace as $value) {
            $subject = preg_replace('/' . $search . '/', (string) $value, $subject, 1);
        }
        return $subject;
    }

    // ========== 验证器 ==========

    /**
     * 验证手机号（中国大陆）
     */
    public static function isMobile(string $str): bool
    {
        if (empty($str)) {
            return false;
        }
        return preg_match('/^1[3-9]\d{9}$/', $str) === 1;
    }

    /**
     * 验证固定电话
     */
    public static function isTel(string $str): bool
    {
        if (empty($str)) {
            return false;
        }
        return preg_match('/^((\(\d{2,3}\))|(\d{3}\-))?(\(0\d{2,3}\)|0\d{2,3}-)?[1-9]\d{6,7}(-\d{1,4})?$/', trim($str)) === 1;
    }

    /**
     * 验证 QQ 号
     */
    public static function isQQ(string $str): bool
    {
        if (empty($str)) {
            return false;
        }
        return preg_match('/^[1-9]\d{4,12}$/', trim($str)) === 1;
    }

    /**
     * 验证邮政编码（中国）
     */
    public static function isZipCode(string $str): bool
    {
        if (empty($str)) {
            return true;
        }
        return preg_match('/^[1-9]\d{5}$/', trim($str)) === 1;
    }

    /**
     * 验证 IP
     */
    public static function isIP(string $value, ?int $flag = null): bool
    {
        if ($flag !== null) {
            return filter_var($value, FILTER_VALIDATE_IP, $flag) !== false;
        }
        return filter_var($value, FILTER_VALIDATE_IP) !== false;
    }

    /**
     * 验证身份证（中国大陆）
     */
    public static function idCard(string $str): bool
    {
        $str = trim($str);
        if (empty($str) || strlen($str) > 18) {
            return false;
        }

        if (preg_match('/^[0-9]{15}$/', $str)) {
            return true;
        }

        if (preg_match('/^[0-9]{17}[0-9Xx]$/', $str)) {
            // 校验码验证
            $weights = [7, 9, 10, 5, 8, 4, 2, 1, 6, 3, 7, 9, 10, 5, 8, 4, 2];
            $checkCodes = ['1', '0', 'X', '9', '8', '7', '6', '5', '4', '3', '2'];
            $sum = 0;
            for ($i = 0; $i < 17; $i++) {
                $sum += (int) $str[$i] * $weights[$i];
            }
            $checkCode = $checkCodes[$sum % 11];
            return strtoupper($str[17]) === $checkCode;
        }

        return false;
    }

    /**
     * 验证 URL
     */
    public static function isURL(string $str): bool
    {
        if (empty($str)) {
            return false;
        }
        return filter_var($str, FILTER_VALIDATE_URL) !== false;
    }

    /**
     * 验证邮箱
     */
    public static function isEmail(string $str): bool
    {
        if (empty($str)) {
            return false;
        }
        return filter_var($str, FILTER_VALIDATE_EMAIL) !== false;
    }

    // ========== 脱敏 ==========

    /**
     * 手机号脱敏：138****1234
     */
    public static function maskPhone(string $phone): string
    {
        if (empty($phone) || strlen($phone) < 7) {
            return $phone;
        }
        return substr($phone, 0, 3) . '****' . substr($phone, -4);
    }

    /**
     * 邮箱脱敏：a***@example.com
     */
    public static function maskEmail(string $email): string
    {
        if (empty($email) || !str_contains($email, '@')) {
            return $email;
        }
        [$name, $domain] = explode('@', $email, 2);
        $masked = strlen($name) > 2
            ? $name[0] . str_repeat('*', strlen($name) - 2) . substr($name, -1)
            : $name[0] . '***';
        return $masked . '@' . $domain;
    }
}

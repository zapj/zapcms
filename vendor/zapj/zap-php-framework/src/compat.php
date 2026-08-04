<?php

/**
 * PHP 7.4 / 8.x 多版本兼容层
 *
 * 提供 PHP 8.0+ 新增函数和接口的 polyfill，
 * 使框架可在 PHP 7.4 环境中运行。
 */

// ── Stringable 接口（PHP 8.0+ 内置） ──
if (!interface_exists('Stringable', false)) {
    interface Stringable
    {
        public function __toString(): string;
    }
}

// ── str_contains（PHP 8.0+ 内置） ──
if (!function_exists('str_contains')) {
    function str_contains(string $haystack, string $needle): bool
    {
        return $needle === '' || strpos($haystack, $needle) !== false;
    }
}

// ── str_starts_with（PHP 8.0+ 内置） ──
if (!function_exists('str_starts_with')) {
    function str_starts_with(string $haystack, string $needle): bool
    {
        return strncmp($haystack, $needle, strlen($needle)) === 0;
    }
}

// ── str_ends_with（PHP 8.0+ 内置） ──
if (!function_exists('str_ends_with')) {
    function str_ends_with(string $haystack, string $needle): bool
    {
        if ($needle === '') {
            return true;
        }
        $needleLen = strlen($needle);
        $haystackLen = strlen($haystack);
        return $needleLen <= $haystackLen
            && substr_compare($haystack, $needle, -$needleLen) === 0;
    }
}

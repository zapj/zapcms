<?php

namespace zap\util;

class Arr
{
    /**
     * 点号分隔取值
     */
    public static function get($array, $key, $default = null)
    {
        if (is_null($key)) {
            return $array;
        }

        if (is_object($array)) {
            $array = (array) $array;
        }

        if (array_key_exists($key, $array)) {
            return $array[$key];
        }

        foreach (explode('.', $key) as $segment) {
            if (!is_array($array) || !array_key_exists($segment, $array)) {
                return $default;
            }
            $array = $array[$segment];
        }
        return $array;
    }

    /**
     * 是否存在
     */
    public static function has(array $array, ?string $key): bool
    {
        if (empty($array) || is_null($key)) {
            return false;
        }
        if (array_key_exists($key, $array)) {
            return true;
        }
        foreach (explode('.', $key) as $segment) {
            if (!is_array($array) || !array_key_exists($segment, $array)) {
                return false;
            }
            $array = $array[$segment];
        }
        return true;
    }

    /**
     * 设置值（点号分隔）
     */
    public static function set(array &$array, ?string $key, $value): array
    {
        if (is_null($key)) {
            return $array = $value;
        }
        $keys = explode('.', $key);
        while (count($keys) > 1) {
            $key = array_shift($keys);
            if (!isset($array[$key]) || !is_array($array[$key])) {
                $array[$key] = [];
            }
            $array = &$array[$key];
        }
        $array[array_shift($keys)] = $value;
        return $array;
    }

    /**
     * 从数组选取指定 keys
     */
    public static function only(array $array, array $keys): array
    {
        $result = [];
        foreach ($keys as $key) {
            if (array_key_exists($key, $array)) {
                $result[$key] = $array[$key];
            }
        }
        return $result;
    }

    /** @deprecated 使用 only() */
    public static function find(array $array, $keys): array
    {
        if (is_string($keys)) {
            return isset($array[$keys]) ? [$keys => $array[$keys]] : [];
        }
        return self::only($array, $keys);
    }

    /**
     * 排除指定 keys
     */
    public static function except(array $array, array $keys): array
    {
        return array_diff_key($array, array_flip($keys));
    }

    /**
     * 提取二维数组指定字段
     */
    public static function pluck(array $array, string $column, ?string $indexKey = null): array
    {
        $result = [];
        foreach ($array as $row) {
            if (!is_array($row) && !is_object($row)) {
                continue;
            }
            $row = (array) $row;
            if (!array_key_exists($column, $row)) {
                continue;
            }
            if ($indexKey !== null && isset($row[$indexKey])) {
                $result[$row[$indexKey]] = $row[$column];
            } else {
                $result[] = $row[$column];
            }
        }
        return $result;
    }

    /**
     * 判断是否关联数组
     */
    public static function isAssoc(array $array): bool
    {
        return array_keys($array) !== range(0, count($array) - 1);
    }

    /**
     * 多维数组扁平化（点号拼接 key）
     */
    public static function dot(array $array, string $prepend = ''): array
    {
        $results = [];
        foreach ($array as $key => $value) {
            if (is_array($value) && !empty($value)) {
                $results = array_merge($results, self::dot($value, $prepend . $key . '.'));
            } else {
                $results[$prepend . $key] = $value;
            }
        }
        return $results;
    }

    /**
     * 从数组中随机取一个或多个元素
     */
    public static function random(array $array, int $count = 1)
    {
        if (empty($array)) {
            return $count === 1 ? null : [];
        }
        $keys = array_rand($array, $count);
        if ($count === 1) {
            return $array[$keys];
        }
        $result = [];
        foreach ((array) $keys as $key) {
            $result[] = $array[$key];
        }
        return $result;
    }

    /**
     * 对数组递归排序
     */
    public static function sortRecursive(array &$array): bool
    {
        foreach ($array as &$value) {
            if (is_array($value)) {
                self::sortRecursive($value);
            }
        }
        unset($value);
        return sort($array);
    }

    /**
     * 数组中某列去重
     */
    public static function unique(array $array, string $column): array
    {
        $seen = [];
        return array_filter($array, function ($item) use ($column, &$seen) {
            $value = is_array($item) ? ($item[$column] ?? null) : ($item->$column ?? null);
            if (in_array($value, $seen, true)) {
                return false;
            }
            $seen[] = $value;
            return true;
        });
    }
}

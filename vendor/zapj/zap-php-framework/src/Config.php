<?php

declare(strict_types=1);

namespace zap;

use zap\util\ZArray;

class Config
{
    protected static ?ZArray $storage = null;

    /** @var array<string, bool> 已加载的配置文件名缓存 */
    protected static array $loaded = [];

    // ========== 内部存储 ==========

    /**
     * 获取底层 ZArray 存储实例
     */
    public static function instance(): ZArray
    {
        if (static::$storage === null) {
            static::$storage = new ZArray();
        }
        return static::$storage;
    }

    // ========== 加载 ==========

    /**
     * 从文件加载配置（幂等：重复加载自动跳过）
     *
     * @param string      $name       配置文件名（不含 .php 后缀）
     * @param string|null $configPath 配置文件目录，默认使用 config_path()
     * @return static
     */
    public static function load(string $name, ?string $configPath = null): Config
    {
        if (isset(static::$loaded[$name])) {
            return new static;
        }

        $filename = ($configPath ?? config_path()) . "{$name}.php";
        if (is_file($filename)) {
            $data = include $filename;
            static::instance()->replace([$name => $data]);
            static::$loaded[$name] = true;
        }

        return new static;
    }

    // ========== 读取 ==========

    /**
     * 获取配置值（支持点语法懒加载）
     *
     * @param string $name    点分键名，如 database.master.host
     * @param mixed  $default 默认值
     * @return mixed
     */
    public static function get(string $name, $default = null)
    {
        $keys = explode('.', $name);
        if (isset($keys[0]) && !static::instance()->has($keys[0])) {
            static::load($keys[0]);
        }

        return static::instance()->get($name, $default);
    }

    /**
     * 检查配置键是否存在（支持点语法）
     */
    public static function has(string $name): bool
    {
        $keys = explode('.', $name);
        if (isset($keys[0]) && !static::instance()->has($keys[0])) {
            static::load($keys[0]);
        }

        return static::instance()->has($name);
    }

    /**
     * 获取全部已加载的配置
     *
     * @return ZArray
     */
    public static function all(): ZArray
    {
        return static::instance();
    }

    // ========== 写入 ==========

    /**
     * 运行时设置配置值（支持点语法深度写入）
     */
    public static function set(string $name, $value): void
    {
        static::instance()->set($name, $value);
    }

    /**
     * 删除配置项（支持点语法深度删除）
     */
    public static function forget(string $name): void
    {
        $keys = explode('.', $name);
        $firstKey = array_shift($keys);

        if (empty($keys)) {
            // 顶层键：直接用 offsetUnset
            static::instance()->offsetUnset($firstKey);
            return;
        }

        // 延迟加载顶层配置文件
        if (!static::instance()->has($firstKey)) {
            static::load($firstKey);
        }

        // 深度删除：读-改-写
        $parent = static::instance()->get($firstKey, []);
        if (!is_array($parent)) {
            return;
        }

        $lastKey = array_pop($keys);
        $current = &$parent;

        foreach ($keys as $key) {
            if (!is_array($current) || !array_key_exists($key, $current)) {
                return;
            }
            $current = &$current[$key];
        }

        if (is_array($current) && array_key_exists($lastKey, $current)) {
            unset($current[$lastKey]);
            static::instance()->set($firstKey, $parent);
        }
    }

    // ========== 缓存管理 ==========

    /**
     * 清空全部配置缓存，强制下次重新加载
     */
    public static function clearCache(): void
    {
        static::$loaded = [];
        static::$storage = null;
    }

    /**
     * clearCache 的语义化别名
     */
    public static function fresh(): void
    {
        static::clearCache();
    }
}

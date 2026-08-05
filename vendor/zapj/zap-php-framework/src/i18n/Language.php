<?php

namespace zap\i18n;

use zap\traits\SingletonTrait;
use zap\util\Str;

class Language
{
    use SingletonTrait;

    /** @var array 已加载的语言消息 */
    public $messages = [];

    /** @var string 当前语言 */
    public $language = 'zh_CN';

    /** @var string 回退语言 */
    public $fallbackLanguage = 'en';

    /** @var string[] 语言文件搜索路径 */
    public $languagePath = [];

    /** @var array 已加载的语言文件标记 */
    protected $loaded = [];

    public function __construct()
    {
        $this->languagePath = [ZAP_SRC . '/resources/languages'];
    }

    // ===================== 配置方法 =====================

    /**
     * 设置 / 获取当前语言
     *
     * @param string|null $language
     * @return string|$this
     */
    public static function locale($language = null)
    {
        if ($language === null) {
            return static::instance()->language;
        }
        static::instance()->language = $language;
        return static::instance();
    }

    /**
     * 设置回退语言
     *
     * @param string $language
     * @return $this
     */
    public static function fallback($language)
    {
        static::instance()->fallbackLanguage = $language;
        return static::instance();
    }

    /**
     * 切换语言
     *
     * @deprecated 使用 Language::locale($language)
     * @param string $language
     */
    public static function useLanguage($language = 'zh-CN')
    {
        static::instance()->language = $language;
        static::addPath(resource_path('languages'));
    }

    /**
     * 获取可用语言列表
     *
     * @return array
     */
    public static function availableLocales()
    {
        $locales = [];
        foreach (static::instance()->languagePath as $path) {
            if (!is_dir($path)) {
                continue;
            }
            foreach (scandir($path) as $dir) {
                if ($dir === '.' || $dir === '..' || !is_dir($path . '/' . $dir)) {
                    continue;
                }
                $locales[$dir] = $dir;
            }
        }
        return array_values($locales);
    }

    // ===================== 路径管理 =====================

    /**
     * 添加语言文件搜索路径
     *
     * @param string $path
     */
    public static function addPath($path)
    {
        if (array_search($path, static::instance()->languagePath) === false) {
            static::instance()->languagePath[] = $path;
        }
    }

    /**
     * 获取所有搜索路径
     *
     * @return array
     */
    public static function getPaths()
    {
        return static::instance()->languagePath;
    }

    // ===================== 消息管理 =====================

    /**
     * 注册 / 批量注册翻译消息
     *
     * @param string|array $key
     * @param mixed|null   $value
     */
    public static function with($key, $value = null)
    {
        if (is_array($key)) {
            foreach ($key as $k => $v) {
                static::instance()->messages[$k] = $v;
            }
        } else {
            static::instance()->messages[$key] = $value;
        }
    }

    /**
     * 设置单个翻译
     *
     * @param string $key
     * @param mixed  $value
     */
    public static function set($key, $value)
    {
        static::instance()->messages[$key] = $value;
    }

    /**
     * 获取翻译原始文本（不做替换）
     *
     * @param string $key      点号分隔的 key，支持嵌套取值
     * @param mixed  $default  未找到时的默认值
     * @return mixed
     */
    public static function get($key, $default = null)
    {
        $instance = static::instance();

        // 点号嵌套 key：validator.rule_email → messages['validator']['rule_email']
        if (str_contains($key, '.') && !isset($instance->messages[$key])) {
            $segments = explode('.', $key);
            $value = $instance->messages;
            foreach ($segments as $segment) {
                if (!is_array($value) || !array_key_exists($segment, $value)) {
                    return $default ?? $key;
                }
                $value = $value[$segment];
            }
            return $value;
        }

        if (isset($instance->messages[$key])) {
            return $instance->messages[$key];
        }

        return $default ?? $key;
    }

    /**
     * 检查翻译 key 是否存在
     *
     * @param string $key
     * @return bool
     */
    public static function has($key)
    {
        if (str_contains($key, '.')) {
            $segments = explode('.', $key);
            $value = static::instance()->messages;
            foreach ($segments as $segment) {
                if (!is_array($value) || !array_key_exists($segment, $value)) {
                    return false;
                }
                $value = $value[$segment];
            }
            return true;
        }
        return array_key_exists($key, static::instance()->messages);
    }

    // ===================== 翻译方法 =====================

    /**
     * 获取翻译文本并支持参数替换
     *
     * @param string $name   文件名.key 格式，如 'validator.rule_email'
     * @param array|string|null $params  替换参数 [key => value] 或替换 {value} 的字符串
     * @param mixed  $value  当 $params 为字符串时的值（已废弃推荐用数组）
     * @return string
     */
    public static function trans($name, $params = null, $value = null)
    {
        [$filename, $msgKey] = explode('.', $name, 2);

        // 按需加载语言文件
        if (!isset(static::instance()->loaded[$filename])) {
            static::load($filename);
        }

        // 使用完整 key 支持嵌套查找：validator.rule_email → messages['validator']['rule_email']
        $message = static::get($name);

        // 回退语言
        if (!is_string($message) || $message === $name) {
            $fallback = static::loadFallback($filename, $msgKey);
            if ($fallback !== null) {
                $message = $fallback;
            }
        }

        if ($params === null) {
            return $message;
        }

        // 兼容旧用法：trans($name, 'scalar_value') → 替换 {value}
        if (!is_array($params)) {
            $params = ['value' => $params];
        }

        return Str::format($message, $params);
    }

    /**
     * 复数翻译
     *
     * @param string $name   文件名.key 格式
     * @param int    $count  数量
     * @param array  $params 替换参数
     * @return string
     */
    public static function transChoice($name, $count, $params = [])
    {
        [$filename, $msgKey] = explode('.', $name, 2);

        if (!isset(static::instance()->loaded[$filename])) {
            static::load($filename);
        }

        // 按复数规则查找：name.one, name.other
        $choice = static::resolvePluralKey($name, $count);

        if ($choice !== null) {
            $message = static::get($choice);
        } else {
            // 尝试 {name}.{count} 精确匹配，否则使用原始 key
            $exactKey = $name . '.' . $count;
            $message = static::get($exactKey);
            if (!is_string($message) || $message === $exactKey) {
                $message = static::get($name);
            }
        }

        // 回退语言
        if (!is_string($message) || $message === $name) {
            $fallback = static::loadFallback($filename, $msgKey);
            if ($fallback !== null) {
                $message = $fallback;
            }
        }

        $params['count'] = $count;
        return Str::format($message, $params);
    }

    // ===================== 内部方法 =====================

    /**
     * 加载语言文件
     *
     * @param string $name 文件名（不含扩展名）
     */
    public static function load($name)
    {
        $instance = static::instance();
        $language = $instance->language;
        $loaded = false;

        foreach ($instance->languagePath as $path) {
            // 尝试 PHP 文件
            $phpFile = $path . "/{$language}/{$name}.php";
            if (is_file($phpFile)) {
                $_LANG = include $phpFile;
                if (is_array($_LANG)) {
                    // 保持嵌套结构：validator.rule_email → messages['validator']['rule_email']
                    static::with([$name => $_LANG]);
                    $loaded = true;
                }
            }

            // 尝试 JSON 文件
            $jsonFile = $path . "/{$language}/{$name}.json";
            if (is_file($jsonFile)) {
                $jsonContent = file_get_contents($jsonFile);
                $jsonData = json_decode($jsonContent, true);
                if (is_array($jsonData)) {
                    static::with([$name => $jsonData]);
                    $loaded = true;
                }
            }
        }

        $instance->loaded[$name] = true;
        return $loaded;
    }

    /**
     * 从回退语言加载翻译
     */
    protected static function loadFallback($filename, $msgKey)
    {
        $instance = static::instance();

        // 已经尝试过回退语言加载
        if (isset($instance->loaded['fallback.' . $filename])) {
            return static::get($filename . '.' . $msgKey);
        }

        $fallbackLanguage = $instance->fallbackLanguage;
        if ($fallbackLanguage === $instance->language) {
            return null;
        }

        foreach ($instance->languagePath as $path) {
            // 尝试 PHP 文件
            $phpFile = $path . "/{$fallbackLanguage}/{$filename}.php";
            if (is_file($phpFile)) {
                $_LANG = include $phpFile;
                if (is_array($_LANG)) {
                    static::with([$filename => $_LANG]);
                }
            }

            // 尝试 JSON 文件
            $jsonFile = $path . "/{$fallbackLanguage}/{$filename}.json";
            if (is_file($jsonFile)) {
                $jsonContent = file_get_contents($jsonFile);
                $jsonData = json_decode($jsonContent, true);
                if (is_array($jsonData)) {
                    static::with([$filename => $jsonData]);
                }
            }
        }

        $instance->loaded['fallback.' . $filename] = true;
        return static::get($filename . '.' . $msgKey);
    }

    /**
     * 根据复数规则解析 key
     *
     * @param string $fullName  完整 key，如 'messages.apples'
     * @param int    $count     数量
     * @return string|null      如 'messages.apples.one' 或 'messages.apples.other'
     */
    protected static function resolvePluralKey($fullName, $count)
    {
        $one = $fullName . '.one';
        if ($count === 1 && static::has($one)) {
            return $one;
        }

        $other = $fullName . '.other';
        if (static::has($other)) {
            return $other;
        }

        return null;
    }
}

<?php

use zap\cms\models\Catalog;
use zap\cms\models\Node;

/**
 * 获取模块路径
 */
function mod_path(string $mod_name): string
{
    return base_path('mods/' . $mod_name);
}

/**
 * 获取单个配置项（带缓存）
 *
 * @param string $option_name
 * @param mixed  $default
 * @param int|null $ttl
 * @return mixed
 */
function get_option(string $option_name, $default = null, ?int $ttl = null)
{
    return \zap\facades\Cache::get($option_name, function () use ($option_name, $default) {
        return \zap\cms\Option::get($option_name, $default);
    }, $ttl);
}

/**
 * 批量获取配置项（带缓存）
 *
 * @param string|string[] $option_name 配置名或数组
 * @param string          $type        匹配类型 (REGEXP / LIKE / =)
 * @param int             $ttl         缓存秒数，默认 10000
 * @return array
 */
function get_options($option_name, string $type = '=', int $ttl = 10000): array
{
    if (is_array($option_name)) {
        $cacheKey = '_opts_' . md5(serialize($option_name));
        return \zap\facades\Cache::get($cacheKey, function () use ($option_name, $type) {
            $result = [];
            foreach ($option_name as $name) {
                $data = \zap\cms\Option::getArray($name, $type);
                $result = array_merge($result, $data);
            }
            return $result;
        }, $ttl);
    }
    return \zap\facades\Cache::get('_opts_' . $option_name, function () use ($option_name, $type) {
        return \zap\cms\Option::getArray($option_name, $type);
    }, $ttl);
}

/**
 * 按点号分隔名取配置值（自动缓存整个分组）
 *
 * @param string $name    配置名，如 'website.title'
 * @param mixed  $default 默认值
 * @return mixed
 *
 * @example option('website.title') → 取 options_website 分组下的 website.title
 */
function option(string $name, $default = null)
{
    $group = strstr($name, '.', true) ?: $name;
    $cacheKey = 'options_' . $group;

    if (!app()->has($cacheKey)) {
        app()->set($cacheKey, get_options($group, 'REGEXP'));
    }

    $options = app()->get($cacheKey);
    return $options[$name] ?? $default;
}

/**
 * 获取 JSON 格式的配置值
 *
 * @param string    $name
 * @param mixed     $default
 * @param bool|null $associative json_decode 的第二个参数
 * @return mixed
 */
function option_get_json(string $name, $default = null, ?bool $associative = null)
{
    $raw = option($name);
    if ($raw === null) {
        return $default;
    }
    $value = json_decode((string) $raw, $associative);
    if (json_last_error() !== JSON_ERROR_NONE) {
        return $default;
    }
    return $value;
}

/**
 * 获取序列化格式的配置值
 *
 * @param string $name
 * @param mixed  $default
 * @return mixed
 */
function option_get_unserialize(string $name, $default = null)
{
    $raw = option($name);
    if ($raw === null) {
        return $default;
    }
    // unserialize 失败返回 false，但 false 也可能是合法值，需二次确认
    $value = unserialize((string) $raw);
    if ($value === false && $raw !== serialize(false)) {
        return $default;
    }
    return $value;
}

/**
 * 页面级状态存储实例
 */
function pageState(): \app\PageState
{
    if (!app()->has('page_state')) {
        app()->page_state = new \app\PageState();
    }
    return app()->page_state;
}

/**
 * 构建绝对 URL（支持变长参数拼接）
 *
 * @param string|array ...$args 路径片段
 * @return string
 *
 * @example url_slug('news', '2024', 'title') → /news/2024/title
 */
function url_slug(...$args): string
{
    $uri = [];
    foreach ($args as $arg) {
        if (empty($arg)) {
            continue;
        }
        $uri[] = is_array($arg) ? implode('/', $arg) : $arg;
    }
    return base_url('/' . implode('/', $uri));
}

/**
 * 站点 URL（优先使用自定义 site_url 配置）
 */
function site_url(string $path = ''): string
{
    $siteUrl = rtrim(config('config.site_url', base_url()), '/');
    return $siteUrl . '/' . ltrim($path, '/');
}

/**
 * 站点首页地址
 */
function home_url(): string
{
    return rtrim(config('config.site_url', base_url()), '/') ?: '/';
}

/**
 * 生成 Node 内容页链接
 *
 * @param int    $nodeId 节点ID
 * @param string $type   节点类型
 * @return string
 *
 * @example node_url(42)           → /node/default?nodeId=42
 * @example node_url(42, 'article') → /node/article?nodeId=42
 */
function node_url(int $nodeId, string $type = 'default'): string
{
    return base_url("node/{$type}?nodeId={$nodeId}");
}

/**
 * 生成外部或自定义链接（自动补全站内地址）
 *
 * @param string $path 链接地址
 * @return string
 */
function url_link(string $path): string
{
    if (preg_match('#^(https?://|//|mailto:|tel:)#i', $path)) {
        return $path;
    }
    return base_url('/' . ltrim($path, '/'));
}

/**
 * 生成栏目链接（<a> 标签）
 *
 * @param string $url   链接地址
 * @param string $title 链接文字
 * @param array  $attrs 额外属性
 * @return string
 */
function catalog_link(string $url, string $title, array $attrs = []): string
{
    $attrStr = '';
    foreach ($attrs as $k => $v) {
        $attrStr .= ' ' . $k . '="' . htmlspecialchars((string) $v, ENT_QUOTES) . '"';
    }
    return '<a href="' . htmlspecialchars($url, ENT_QUOTES) . '"' . $attrStr . '>' . htmlspecialchars($title) . '</a>';
}

/**
 * 主题资源 URL
 */
function theme_url(?string $path = null): string
{
    $theme = option('website.theme', 'basic');
    return site_url("themes/{$theme}/{$path}");
}

/**
 * 主题资源文件系统路径
 */
function theme_path(?string $path = null): string
{
    $theme = option('website.theme', 'basic');
    return base_path("themes/{$theme}/{$path}");
}

/**
 * 检查主题文件是否存在（自动尝试多种扩展名）
 *
 * @param string               $file    文件名（不含扩展名）
 * @param string|string[]|null $extList 扩展名列表，默认 ['.php', '.twig']
 * @return bool
 */
function theme_file_is_exists(string $file, $extList = null): bool
{
    if ($extList === null) {
        $extList = ['.php', '.twig'];
    }
    if (!is_array($extList)) {
        $extList = [$extList];
    }
    foreach ($extList as $ext) {
        if (is_file(theme_path($file . $ext))) {
            return true;
        }
    }
    return false;
}

/**
 * 美化输出数组（短数组语法）
 *
 * @param mixed $expression
 * @param bool  $return     是否返回字符串而非直接输出
 * @return string
 */
function zap_var_export($expression, bool $return = false): string
{
    $export = var_export($expression, true);
    $patterns = [
        "/array \(/"                => '[',
        "/^([ ]*)\)(,?)$/m"         => '$1]$2',
        "/=>[ ]?\n[ ]+\[/"          => '=> [',
        "/([ ]*)(\'[^\']+\') => ([\[\'])/" => '$1$2 => $3',
    ];
    $export = preg_replace(array_keys($patterns), array_values($patterns), $export);
    if (!$return) {
        echo $export;
    }
    return $export;
}

/**
 * 条件为真时输出（简化模板 if 判断）
 *
 * @param mixed  $expr
 * @param string $str
 */
function if_echo($expr, string $str): void
{
    if ($expr) {
        echo $str;
    }
}

/**
 * 三目输出（简化模板 if-else 判断）
 *
 * @param mixed  $expr
 * @param string $str
 * @param string $str2
 */
function if_else_echo($expr, string $str, string $str2): void
{
    if ($expr) {
        echo $str;
    } else {
        echo $str2;
    }
}

/**
 * 条件为真时返回值
 *
 * @param mixed  $expr
 * @param string $str
 * @return string|null
 */
function if_return($expr, string $str): ?string
{
    return $expr ? $str : null;
}

/**
 * 获取当前主题名称
 *
 * @return string|false
 */
function get_theme_name()
{
    $theme = config('config.theme', 'basic');
    return $theme === false ? false : $theme;
}

/**
 * 获取主题文件系统路径（兼容旧函数，推荐使用 theme_path）
 *
 * @param string|null $path
 * @return string|false
 */
function get_theme_path(?string $path = null)
{
    $theme = config('config.theme', 'basic');
    if ($theme === false) {
        return false;
    }
    return $path === null
        ? base_path("themes/{$theme}")
        : base_path("themes/{$theme}/{$path}");
}

/**
 * 获取主题资源 URL（兼容旧函数，推荐使用 theme_url）
 *
 * @param string|null $path
 * @return string|false
 */
function get_theme_url(?string $path = null)
{
    $theme = config('config.theme', 'basic');
    if ($theme === false) {
        return false;
    }
    return $path === null
        ? base_url("themes/{$theme}")
        : base_url("themes/{$theme}/{$path}");
}

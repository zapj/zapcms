<?php

declare(strict_types=1);

/**
 * ZAP Framework 全局辅助函数
 *
 * 提供路径、配置、Session、资源、Hook、URL、翻译等高频操作的便捷封装。
 */

use zap\App;
use zap\component\Hooks;
use zap\Config;
use zap\ErrorHandler;
use zap\http\Response;
use zap\http\Session;
use zap\http\Uri;
use zap\http\ZapRequest;
use zap\util\Arr;
use zap\util\Str;

// ============================================================
//   常量
// ============================================================

const Z_DAY = 86400;
const Z_HOUR = 3600;

const Z_DATE_TIME = 'Y-m-d H:i:s';
const Z_DATE = 'Y-m-d';
const Z_TIME = 'H:i:s';

const FLASH_INFO = 'info';
const FLASH_WARNING = 'warning';
const FLASH_ERROR = 'error';
const FLASH_SUCCESS = 'success';

const ASSETS_HEAD = 'assets_head_urls';
const ASSETS_HEAD_TEXT = 'assets_head_text';
const ASSETS_BODY = 'assets_body_urls';
const ASSETS_BODY_TEXT = 'assets_body_text';

const FETCH_LAZY = 1;
const FETCH_ASSOC = 2;
const FETCH_NUM = 3;
const FETCH_BOTH = 4;
const FETCH_OBJ = 5;
const FETCH_BOUND = 6;
const FETCH_COLUMN = 7;
const FETCH_CLASS = 8;
const FETCH_INTO = 9;
const FETCH_FUNC = 10;
const FETCH_GROUP = 65536;
const FETCH_UNIQUE = 196608;
const FETCH_KEY_PAIR = 12;

// ============================================================
//   App / 路径
// ============================================================

if (!function_exists('app')) {
    /**
     * 获取 App 实例、从容器读取或写入容器
     *
     * @param string|null $name  容器键名（null 返回 App 实例）
     * @param mixed       $value 非 null 时注册到容器
     * @return App|mixed
     */
    function app(?string $name = null, $value = null)
    {
        $app = App::instance();

        if ($name === null) {
            return $app;
        }

        if (func_num_args() > 1) {
            $app->set($name, $value);
            return true;
        }

        return $app->get($name);
    }
}

if (!function_exists('base_path')) {
    function base_path(string $path = ''): string
    {
        return App::instance()->basePath($path);
    }
}

if (!function_exists('root_path')) {
    function root_path(string $path = ''): string
    {
        return App::instance()->rootPath($path);
    }
}

if (!function_exists('config_path')) {
    function config_path(string $filename = ''): string
    {
        return App::instance()->configPath($filename);
    }
}

if (!function_exists('storage_path')) {
    function storage_path(string $filename = ''): string
    {
        return App::instance()->storagePath($filename);
    }
}

if (!function_exists('resource_path')) {
    function resource_path(string $filename = ''): string
    {
        return App::instance()->resourcesPath($filename);
    }
}

if (!function_exists('assets_path')) {
    function assets_path(string $filename = ''): string
    {
        return App::instance()->assetsPath($filename);
    }
}

if (!function_exists('themes_path')) {
    function themes_path(string $filename = ''): string
    {
        return App::instance()->themesPath($filename);
    }
}

if (!function_exists('var_path')) {
    function var_path(string $filename = ''): string
    {
        return App::instance()->varPath($filename);
    }
}

if (!function_exists('public_path')) {
    /**
     * 获取 public 入口目录路径
     */
    function public_path(string $filename = ''): string
    {
        return App::instance()->publicPath($filename);
    }
}

if (!function_exists('is_production')) {
    /**
     * 判断是否为生产环境（config.debug 为 false）
     */
    function is_production(): bool
    {
        return App::instance()->isProduction();
    }
}

// ============================================================
//   配置
// ============================================================

if (!function_exists('config')) {
    /**
     * 获取配置值（支持点语法：config.key）
     *
     * @param string $name    配置键名
     * @param mixed  $default 默认值
     * @return mixed
     */
    function config(string $name, $default = null)
    {
        return Config::get($name, $default);
    }
}

if (!function_exists('config_set')) {
    function config_set(string $name, $value): void
    {
        Config::set($name, $value);
    }
}

if (!function_exists('config_has')) {
    function config_has(string $name): bool
    {
        return Config::has($name);
    }
}

if (!function_exists('config_forget')) {
    /**
     * 删除配置项（支持点语法）
     */
    function config_forget(string $name): void
    {
        Config::forget($name);
    }
}

if (!function_exists('config_all')) {
    /**
     * 获取全部已加载配置
     *
     * @return array<string, \ArrayObject>
     */
    function config_all(): array
    {
        return Config::all();
    }
}

if (!function_exists('config_clear')) {
    function config_clear(): void
    {
        Config::clearCache();
    }
}

// ============================================================
//   URL
// ============================================================

if (!function_exists('base_url')) {
    function base_url(string $url = ''): string
    {
        return App::instance()->baseUrl($url);
    }
}

if (!function_exists('themes_url')) {
    function themes_url(string $url = ''): string
    {
        return App::instance()->themesUrl($url);
    }
}

if (!function_exists('site_url_lang')) {
    /**
     * 根据当前语言前缀生成完整 URL
     */
    function site_url_lang(string $url = ''): string
    {
        $lang = config('config.lang');
        if ($lang) {
            return base_url($lang . '/' . ltrim($url, '/'));
        }
        return base_url(ltrim($url, '/'));
    }
}

if (!function_exists('url_to')) {
    function url_to(string $url, array $params = [], bool $queryString = true): string
    {
        return \zap\facades\Url::to($url, $params, $queryString);
    }
}

if (!function_exists('url_action')) {
    function url_action(string $controller, $queryParams = null, $pathParams = null): string
    {
        return \zap\facades\Url::action($controller, $queryParams, $pathParams);
    }
}

if (!function_exists('current_url')) {
    function current_url(): string
    {
        return Uri::current();
    }
}

if (!function_exists('redirect')) {
    /**
     * 302/301 重定向并终止脚本
     */
    function redirect(string $url, int $statusCode = 302): void
    {
        Response::redirect($url, $statusCode);
        exit;
    }
}

if (!function_exists('route')) {
    function route(string $name, array $args = []): ?string
    {
        return \zap\http\Router::getRouteUrl($name, $args);
    }
}

if (!function_exists('replace_route_args')) {
    function replace_route_args(string $path, array $args = []): string
    {
        foreach ($args as $key => $val) {
            $path = str_replace('{' . $key . '}', (string)$val, $path);
        }
        return $path;
    }
}

// ============================================================
//   资源管理（Assets）
// ============================================================

if (!function_exists('register_scripts')) {
    /**
     * 注册 JS 脚本（自动存储到容器，按位置分组）
     *
     * @param string|array $urls   单个 URL 或 URL 数组
     * @param string       $position ASSETS_HEAD | ASSETS_BODY
     */
    function register_scripts($urls, string $position = ASSETS_HEAD): void
    {
        $key = 'scripts_' . $position;
        if (!app()->has($key)) {
            app()->$key = new \ArrayObject([], \ArrayObject::ARRAY_AS_PROPS);
        }

        if (is_array($urls)) {
            foreach ($urls as $url) {
                app()->$key[] = $url;
            }
        } else {
            app()->$key[] = $urls;
        }
    }
}

if (!function_exists('register_styles')) {
    /**
     * 注册 CSS 样式
     *
     * @param string|array $urls   单个 URL 或 URL 数组
     * @param string       $position ASSETS_HEAD | ASSETS_BODY
     */
    function register_styles($urls, string $position = ASSETS_HEAD): void
    {
        $key = 'styles_' . $position;
        if (!app()->has($key)) {
            app()->$key = new \ArrayObject([], \ArrayObject::ARRAY_AS_PROPS);
        }

        if (is_array($urls)) {
            foreach ($urls as $url) {
                app()->$key[] = $url;
            }
        } else {
            app()->$key[] = $urls;
        }
    }
}

if (!function_exists('print_scripts')) {
    /**
     * 输出已注册的 JS 标签
     *
     * @param string $position ASSETS_HEAD / ASSETS_BODY / ASSETS_HEAD_TEXT / ASSETS_BODY_TEXT
     */
    function print_scripts(string $position = ASSETS_HEAD): void
    {
        $key = 'scripts_' . $position;
        if (!app()->has($key)) {
            return;
        }

        if ($position === ASSETS_HEAD || $position === ASSETS_BODY) {
            // 外部脚本
            foreach (app()->get($key) as $script) {
                echo '<script src="', esc((string)$script), '"></script>', "\n";
            }
        } elseif ($position === ASSETS_HEAD_TEXT || $position === ASSETS_BODY_TEXT) {
            // 内联脚本
            echo '<script type="text/javascript">', "\n";
            echo '//<![CDATA[', "\n";
            foreach (app()->get($key) as $script) {
                echo (string)$script, "\n";
            }
            echo '//]]>', "\n";
            echo '</script>', "\n";
        }
    }
}

if (!function_exists('print_styles')) {
    /**
     * 输出已注册的 CSS 标签（同时输出 HEAD 和 HEAD_TEXT）
     */
    function print_styles(): void
    {
        if (app()->has('styles_' . ASSETS_HEAD)) {
            foreach (app()->get('styles_' . ASSETS_HEAD) as $style) {
                echo '<link rel="stylesheet" href="', esc((string)$style), '">', "\n";
            }
        }

        if (app()->has('styles_' . ASSETS_HEAD_TEXT)) {
            echo '<style>', "\n";
            foreach (app()->get('styles_' . ASSETS_HEAD_TEXT) as $style) {
                echo (string)$style, "\n";
            }
            echo '</style>', "\n";
        }
    }
}

// ============================================================
//   视图
// ============================================================

if (!function_exists('view')) {
    function view(string $template, array $data = [], bool $return = false): ?string
    {
        return \zap\view\View::render($template, $data, $return);
    }
}

// ============================================================
//   Session / Flash / 表单
// ============================================================

if (!function_exists('session')) {
    function session(): Session
    {
        return Session::getInstance();
    }
}

if (!function_exists('set_session')) {
    function set_session(string $name, $value): void
    {
        Session::put($name, $value);
    }
}

if (!function_exists('get_session')) {
    function get_session(string $name, $default = null)
    {
        return Session::get($name, $default);
    }
}

if (!function_exists('has_session')) {
    function has_session(string $name): bool
    {
        return Session::getInstance()->has($name);
    }
}

if (!function_exists('remove_session')) {
    function remove_session(string $name): void
    {
        Session::forget($name);
    }
}

if (!function_exists('add_flash')) {
    function add_flash(string $message, string $type = FLASH_INFO): Session
    {
        return Session::getInstance()->add_flash($message, $type);
    }
}

if (!function_exists('get_flash')) {
    /**
     * @param string|null $type  消息类型，null 返回全部
     * @param bool        $first true 仅返回第一条
     * @return array|string|false
     */
    function get_flash(?string $type = null, bool $first = false)
    {
        if ($type !== null && $first) {
            $flash = Session::getFlash($type);
            return $flash ? current($flash) : false;
        }
        return Session::getFlash($type);
    }
}

if (!function_exists('has_flash')) {
    function has_flash(?string $type = null): bool
    {
        return Session::getInstance()->hasFlash($type);
    }
}

if (!function_exists('clear_flash')) {
    function clear_flash(?string $type = null): array
    {
        return Session::getInstance()->clearFlash($type);
    }
}

if (!function_exists('old')) {
    function old(?string $key = null, $default = null)
    {
        return Session::old($key, $default);
    }
}

if (!function_exists('csrf_token')) {
    function csrf_token(): string
    {
        return Session::token();
    }
}

if (!function_exists('csrf_field')) {
    function csrf_field(): string
    {
        return '<input type="hidden" name="_token" value="' . esc(Session::token()) . '" />';
    }
}

if (!function_exists('flash')) {
    /**
     * 设置或获取 Flash 消息
     *
     * @param string|null $message 消息文本（null 时读取）
     * @param string      $type    消息类型
     * @return string|null
     */
    function flash(?string $message = null, string $type = 'success'): ?string
    {
        if ($message !== null) {
            Session::flash($type, $message);
            return null;
        }
        return Session::getFlash($type);
    }
}

// ============================================================
//   HTTP / 错误处理
// ============================================================

if (!function_exists('req')) {
    function req(): ZapRequest
    {
        return ZapRequest::instance();
    }
}

if (!function_exists('abort')) {
    /**
     * 中断请求并抛出 HTTP 异常
     *
     * @param int    $statusCode HTTP 状态码
     * @param string $message    错误消息
     * @param array  $headers    额外响应头
     * @return never
     * @throws \zap\exception\HttpException
     */
    function abort(int $statusCode = 500, string $message = '', array $headers = []): void
    {
        ErrorHandler::abort($statusCode, $message, $headers);
    }
}

if (!function_exists('report')) {
    /**
     * 报告异常（仅记录日志，不渲染）
     */
    function report(\Throwable $e): void
    {
        ErrorHandler::instance()->report($e);
    }
}

if (!function_exists('response')) {
    function response($content = null, int $statusCode = 200, ?array $headers = []): Response
    {
        return new Response($content, $statusCode, $headers);
    }
}

// ============================================================
//   字符串 / 输出
// ============================================================

if (!function_exists('_e')) {
    /**
     * HTML 特殊字符转义（防 XSS）
     *
     * @param string      $html    待转义文本
     * @param string|null $charset 字符集，默认 UTF-8
     */
    function _e(string $html, ?string $charset = null): string
    {
        $charset ??= config('config.charset', 'UTF-8');
        return htmlspecialchars($html, ENT_QUOTES, $charset);
    }
}

if (!function_exists('esc')) {
    /**
     * _e 的语义化别名
     */
    function esc(?string $str, ?string $charset = null): string
    {
        return _e($str ?? '', $charset);
    }
}

if (!function_exists('_echo')) {
    /**
     * 输出转义后的 HTML 并换行
     */
    function _echo(string $text): void
    {
        echo _e($text) . "<br/>\n";
    }
}

if (!function_exists('sanitize')) {
    /**
     * 生成 URL 友好别名（slugify）
     *
     * @param string $title 原始标题
     * @return string 如 "Hello World" => "hello-world"
     */
    function sanitize(string $title): string
    {
        $title = strip_tags($title);
        // Preserve escaped octets
        $title = preg_replace('|%([a-fA-F0-9][a-fA-F0-9])|', '---$1---', $title);
        $title = str_replace('%', '', $title);
        $title = preg_replace('|---([a-fA-F0-9][a-fA-F0-9])---|', '%$1', $title);

        if (seems_utf8($title)) {
            if (function_exists('mb_strtolower')) {
                $title = mb_strtolower($title, 'UTF-8');
            }
            $title = utf8_uri_encode($title, 200);
        }

        $title = strtolower($title);
        $title = preg_replace('/&.+?;/', '', $title); // kill entities
        $title = str_replace('.', '-', $title);
        $title = preg_replace('/[^%a-z0-9 _-]/', '', $title);
        $title = preg_replace('/\s+/', '-', $title);
        $title = preg_replace('|-+|', '-', $title);
        return trim($title, '-');
    }
}

if (!function_exists('str')) {
    /**
     * 创建 Str 实例（链式字符串操作）
     */
    function str(string $string = ''): Str
    {
        return new Str($string);
    }
}

if (!function_exists('zap_pp')) {
    /**
     * 格式化打印变量（调试用，输出后终止脚本）
     */
    function zap_pp(...$params): void
    {
        echo '<pre class="prettyprint" style="background:#000;border-radius:6px;color:#fff;padding:15px;font-size:14px;">';
        foreach ($params as $value) {
            echo _e(print_r($value, true));
        }
        echo '</pre>';
    }
}

if (!function_exists('zap_print_r')) {
    function zap_print_r(array $data, bool $die = true): void
    {
        echo '<pre>';
        print_r($data);
        echo '</pre>';
        if ($die) {
            exit;
        }
    }
}

// ============================================================
//   UTF-8 / 编码
// ============================================================

if (!function_exists('utf8_uri_encode')) {
    function utf8_uri_encode(string $utf8_string, int $length = 0): string
    {
        $unicode = '';
        $values = [];
        $num_octets = 1;
        $unicode_length = 0;

        $string_length = strlen($utf8_string);
        for ($i = 0; $i < $string_length; $i++) {
            $value = ord($utf8_string[$i]);

            if ($value < 128) {
                if ($length && ($unicode_length >= $length)) {
                    break;
                }
                $unicode .= chr($value);
                $unicode_length++;
            } else {
                if (count($values) === 0) {
                    $num_octets = ($value < 224) ? 2 : 3;
                }

                $values[] = $value;

                if ($length && ($unicode_length + ($num_octets * 3)) > $length) {
                    break;
                }
                if (count($values) === $num_octets) {
                    if ($num_octets === 3) {
                        $unicode .= '%' . dechex($values[0]) . '%' . dechex($values[1]) . '%' . dechex($values[2]);
                        $unicode_length += 9;
                    } else {
                        $unicode .= '%' . dechex($values[0]) . '%' . dechex($values[1]);
                        $unicode_length += 6;
                    }

                    $values = [];
                    $num_octets = 1;
                }
            }
        }

        return $unicode;
    }
}

if (!function_exists('seems_utf8')) {
    function seems_utf8(string $str): bool
    {
        $length = strlen($str);
        for ($i = 0; $i < $length; $i++) {
            $c = ord($str[$i]);
            if ($c < 0x80)          $n = 0;
            elseif (($c & 0xE0) === 0xC0) $n = 1;
            elseif (($c & 0xF0) === 0xE0) $n = 2;
            elseif (($c & 0xF8) === 0xF0) $n = 3;
            elseif (($c & 0xFC) === 0xF8) $n = 4;
            elseif (($c & 0xFE) === 0xFC) $n = 5;
            else                 return false;

            for ($j = 0; $j < $n; $j++) {
                if ((++$i === $length) || ((ord($str[$i]) & 0xC0) !== 0x80)) {
                    return false;
                }
            }
        }
        return true;
    }
}

if (!function_exists('base64_url_encode')) {
    function base64_url_encode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }
}

if (!function_exists('base64_url_decode')) {
    function base64_url_decode(string $base64Url): string
    {
        return base64_decode(strtr($base64Url, '-_', '+/'));
    }
}

// ============================================================
//   数组 / 对象
// ============================================================

if (!function_exists('object_get')) {
    function object_get($object, ?string $key, $default = null)
    {
        if ($key === null || trim($key) === '') {
            return $object;
        }
        foreach (explode('.', $key) as $segment) {
            if (!is_object($object) || !isset($object->{$segment})) {
                return $default;
            }
            $object = $object->{$segment};
        }
        return $object;
    }
}

if (!function_exists('arr_get')) {
    function arr_get(array &$array, string $key, $default = null, ?callable $type = null)
    {
        $value = Arr::get($array, $key, $default);
        return $type ? $type($value) : $value;
    }
}

if (!function_exists('arr_has')) {
    function arr_has(array &$array, string $key): bool
    {
        return Arr::has($array, $key);
    }
}

if (!function_exists('arr_set')) {
    function arr_set(array &$array, string $key, $value): void
    {
        Arr::set($array, $key, $value);
    }
}

if (!function_exists('arrayToObject')) {
    /**
     * 递归地将数组转换为 stdClass
     */
    function arrayToObject($array)
    {
        if (!is_array($array)) {
            return $array;
        }

        $object = new \stdClass();
        foreach ($array as $name => $value) {
            $name = strtolower(trim((string)$name));
            if ($name !== '') {
                $object->$name = arrayToObject($value);
            }
        }
        return $object;
    }
}

if (!function_exists('objectToArray')) {
    /**
     * 递归地将对象转换为数组
     */
    function objectToArray($obj): array
    {
        return json_decode(json_encode($obj, JSON_THROW_ON_ERROR), true, 512, JSON_THROW_ON_ERROR);
    }
}

if (!function_exists('is_assoc')) {
    function is_assoc(array $array): bool
    {
        return is_array($array) && (array_values($array) !== $array);
    }
}

// ============================================================
//   ZArray 实例
// ============================================================

if (!function_exists('zarray')) {
    function zarray($data = []): \zap\util\ZArray
    {
        return new \zap\util\ZArray($data);
    }
}

// ============================================================
//   日志 / 翻译 / 数据库
// ============================================================

if (!function_exists('logger')) {
    /**
     * 获取日志记录器
     *
     * @param string $name 通道名
     * @return \Monolog\Logger|\zap\log\SimpleLogger
     * @throws \Exception
     */
    function logger(string $name = 'app')
    {
        return App::instance()->getLogger($name);
    }
}

if (!function_exists('trans')) {
    function trans(string $key, $params = null, $value = null): string
    {
        return \zap\i18n\Language::trans($key, $params, $value);
    }
}

if (!function_exists('__')) {
    /**
     * 翻译（推荐用法）
     *
     * @param string $key    文件名.key 格式
     * @param array  $params 替换参数
     */
    function __(string $key, array $params = []): string
    {
        return \zap\i18n\Language::trans($key, $params);
    }
}

if (!function_exists('trans_choice')) {
    /**
     * 复数翻译
     *
     * @param string $key    文件名.key 格式
     * @param int    $count  数量
     * @param array  $params 替换参数
     */
    function trans_choice(string $key, int $count, array $params = []): string
    {
        return \zap\i18n\Language::transChoice($key, $count, $params);
    }
}

// ============================================================
//   Hook 系统
// ============================================================

if (!function_exists('add_filter')) {
    function add_filter(string $hookName, callable $callback, int $priority = 10): Hooks
    {
        return Hooks::instance()->add_filter($hookName, $callback, $priority);
    }
}

if (!function_exists('add_action')) {
    function add_action(string $hookName, callable $callback, int $priority = 10): Hooks
    {
        return Hooks::instance()->add_action($hookName, $callback, $priority);
    }
}

if (!function_exists('apply_filters')) {
    function apply_filters(string $hookName, $value, ...$args)
    {
        return Hooks::instance()->apply_filters($hookName, $value, ...$args);
    }
}

if (!function_exists('do_action')) {
    function do_action(string $hookName, ...$args): void
    {
        Hooks::instance()->do_action($hookName, ...$args);
    }
}

if (!function_exists('remove_filter')) {
    function remove_filter(string $hookName, callable $callback, int $priority = 10): void
    {
        Hooks::instance()->remove_filter($hookName, $callback, $priority);
    }
}

if (!function_exists('remove_action')) {
    function remove_action(string $hookName, callable $callback, int $priority = 10): void
    {
        Hooks::instance()->remove_action($hookName, $callback, $priority);
    }
}

if (!function_exists('remove_all_filter')) {
    function remove_all_filter(string $hookName): void
    {
        Hooks::instance()->remove_all_filter($hookName);
    }
}

if (!function_exists('remove_all_action')) {
    function remove_all_action(string $hookName): void
    {
        Hooks::instance()->remove_all_action($hookName);
    }
}

if (!function_exists('event_fire')) {
    /**
     * 触发事件（先 include 事件文件，再调用 Hook 链）
     *
     * @param string $eventName 事件名称
     * @param mixed  ...$args   传递给处理器的参数
     */
    function event_fire(string $eventName, ...$args): void
    {
        $eventsDir = base_path('events/');
        $eventFile = $eventsDir . $eventName . '.php';
        if (is_file($eventFile)) {
            include_once $eventFile;
        }
        do_action($eventName, ...$args);
    }
}

// ============================================================
//   APCu 缓存
// ============================================================

if (!function_exists('zap_cache_get')) {
    function zap_cache_get(string $key)
    {
        return apcu_fetch($key);
    }
}

if (!function_exists('zap_cache_set')) {
    function zap_cache_set(string $key, $value, int $ttl = 0): bool
    {
        return apcu_store($key, $value, $ttl);
    }
}

if (!function_exists('zap_cache_del')) {
    function zap_cache_del(string $key): bool
    {
        return apcu_delete($key);
    }
}

if (!function_exists('zap_cache_clear')) {
    function zap_cache_clear(): bool
    {
        return apcu_clear_cache();
    }
}

if (!function_exists('zap_cache_enabled')) {
    function zap_cache_enabled(): bool
    {
        return function_exists('apcu_enabled') && apcu_enabled();
    }
}

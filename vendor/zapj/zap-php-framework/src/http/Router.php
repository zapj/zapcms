<?php

namespace zap\http;

use \Exception;
use zap\cache\CacheInterface;
use zap\cache\FileCache;
use zap\cache\RedisCache;
use zap\cache\MemcacheCache;

class Router
{
    /** @var array 已注册的路由 */
    public $routes = [];

    /** @var array 当前路由参数（匹配后填充） */
    public array $params = [];

    /** @var array|null 当前匹配的路由信息（兼容旧版） */
    public $currentRoute = null;

    /** @var string|null 当前请求路径 */
    protected ?string $requestPath = null;

    /** @var callable|string|null notFound 处理器 */
    protected $_notfound;

    /** @var array 命名路由表 ['name' => 'pattern'] */
    protected static array $namedRoutes = [];

    /** @var array 路由组属性栈 */
    protected array $groupStack = [];

    /** @var int 当前分组内路由计数 */
    protected int $groupRouteCount = 0;

    /** @var CacheInterface|null 路由缓存驱动 */
    protected static ?CacheInterface $cacheDriver = null;

    /** @var string 缓存 key */
    protected static string $cacheKey = 'zap.routes.cache';

    /** @var string 缓存驱动类型 ('file'|'redis'|'memcached'|'memcache') */
    protected static string $cacheDriverType = 'file';

    // ───────────────────── HTTP 方法快捷注册 ─────────────────────

    public function get(string $pattern, $fn): Route
    {
        return $this->match(['GET', 'HEAD'], $pattern, $fn);
    }

    public function post(string $pattern, $fn): Route
    {
        return $this->match(['POST'], $pattern, $fn);
    }

    public function put(string $pattern, $fn): Route
    {
        return $this->match(['PUT'], $pattern, $fn);
    }

    public function patch(string $pattern, $fn): Route
    {
        return $this->match(['PATCH'], $pattern, $fn);
    }

    public function delete(string $pattern, $fn): Route
    {
        return $this->match(['DELETE'], $pattern, $fn);
    }

    public function options(string $pattern, $fn): Route
    {
        return $this->match(['OPTIONS'], $pattern, $fn);
    }

    /**
     * 注册任意 HTTP 方法路由
     */
    public function any(string $pattern, $fn): Route
    {
        return $this->match(['GET', 'HEAD', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'], $pattern, $fn);
    }

    /**
     * 注册多条 HTTP 方法的路由
     */
    public function match(array $methods, string $pattern, $fn): Route
    {
        $pattern = $this->applyGroupPrefix($pattern);
        $route = new Route($pattern, $fn, $methods);
        $this->routes[] = $route;
        $this->groupRouteCount++;
        return $route;
    }

    // ───────────────────── 路由组 ─────────────────────

    /**
     * 路由分组
     */
    public function group(array $attributes, callable $callback): void
    {
        $this->groupStack[] = $attributes;
        $this->groupRouteCount = 0;
        $callback($this);
        array_pop($this->groupStack);
    }

    /**
     * 应用分组前缀和中间件
     */
    private function applyGroupPatterns(Route $route): void
    {
        foreach ($this->groupStack as $group) {
            if (isset($group['middleware'])) {
                $mw = $group['middleware'];
                if (is_string($mw)) {
                    $route->middleware($mw);
                } elseif (is_array($mw)) {
                    foreach ($mw as $m) {
                        $route->middleware($m);
                    }
                }
            }
        }
    }

    /**
     * 应用分组前缀
     */
    private function applyGroupPrefix(string $pattern): string
    {
        if (empty($this->groupStack)) {
            return $pattern;
        }
        $prefix = '';
        foreach ($this->groupStack as $group) {
            if (isset($group['prefix'])) {
                $prefix .= '/' . trim($group['prefix'], '/');
            }
        }
        return $prefix . '/' . ltrim($pattern, '/');
    }

    // ───────────────────── 资源路由 ─────────────────────

    /**
     * 注册 RESTful 资源路由
     *
     * @param string $name       资源名称
     * @param string $controller 控制器类名
     * @param array  $options    选项 ['only'=>[...], 'except'=>[...]]
     */
    public function resource(string $name, string $controller, array $options = []): void
    {
        $actions = [
            'index'   => ['get',    "/{$name}",               '@index'],
            'create'  => ['get',    "/{$name}/create",        '@create'],
            'save'    => ['post',   "/{$name}",               '@save'],
            'show'    => ['get',    "/{$name}/{id:\d+}",      '@show'],
            'edit'    => ['get',    "/{$name}/{id:\d+}/edit", '@edit'],
            'update'  => ['put',    "/{$name}/{id:\d+}",      '@update'],
            'destroy' => ['delete', "/{$name}/{id:\d+}",      '@destroy'],
        ];

        // 仅注册指定动作
        if (isset($options['only'])) {
            $actions = array_intersect_key($actions, array_flip((array)$options['only']));
        }
        // 排除指定动作
        if (isset($options['except'])) {
            $actions = array_diff_key($actions, array_flip((array)$options['except']));
        }

        foreach ($actions as $action => [$method, $pattern, $suffix]) {
            $route = $this->$method($pattern, $controller . $suffix);
            $route->name("{$name}.{$action}");
        }
    }

    // ───────────────────── 命名路由 ─────────────────────

    /**
     * 注册命名路由（用于 URL 生成）
     */
    public function name(string $name, string $pattern): void
    {
        static::$namedRoutes[$name] = $pattern;
    }

    /**
     * 根据路由名称生成 URL
     *
     * @param string $name   路由名称
     * @param array  $params 参数替换 ['id' => 5]
     * @return string
     */
    public static function url(string $name, array $params = []): string
    {
        if (!isset(static::$namedRoutes[$name])) {
            throw new \InvalidArgumentException("Named route '{$name}' not found.");
        }

        $url = static::$namedRoutes[$name];

        // 替换 {param} 占位符
        foreach ($params as $key => $value) {
            $url = preg_replace('/\{' . preg_quote($key, '/') . '(:[^}]+)?\}/', (string)$value, $url);
        }

        // 移除未填充的可选参数
        $url = preg_replace('/\{[^}]+\}/', '', $url);

        // 清理多余的 /
        $url = preg_replace('#/+#', '/', $url);

        return $url;
    }

    /**
     * 获取所有命名路由
     */
    public static function getNamedRoutes(): array
    {
        return static::$namedRoutes;
    }

    // ───────────────────── NotFound ─────────────────────

    /**
     * 注册 404 处理器
     *
     * @param callable|string $handler 回调或 'Controller@method'
     */
    public function setNotFound($handler): void
    {
        $this->_notfound = $handler;
    }

    /**
     * 获取 404 处理器
     *
     * @return callable|string|null
     */
    public function getNotFound()
    {
        return $this->_notfound;
    }

    // ───────────────────── 调度 ─────────────────────

    /**
     * 匹配并执行路由
     *
     * @param string $requestUrl    请求 URL（已解析）
     * @param string $requestMethod HTTP 方法
     * @return bool
     */
    public function dispatch(string $requestUrl, string $requestMethod = 'GET'): bool
    {
        // 存储请求路径（去掉查询参数）
        $this->requestPath = strtok($requestUrl, '?') ?: '/';

        // 也清理掉 $requestUrl 中的查询参数，避免被 {any:.*} 通配符捕获
        $requestUrl = $this->requestPath;

        $matched = false;

        // HEAD 请求复用 GET 路由
        if ($requestMethod === 'HEAD') {
            if (!ob_get_level()) {
                ob_start();
            }
            $matched = $this->dispatchInternal($requestUrl, 'GET', true);
        } else {
            $matched = $this->dispatchInternal($requestUrl, $requestMethod, false);
        }

        return $matched;
    }

    /**
     * 内部调度逻辑
     */
    private function dispatchInternal(string $requestUrl, string $requestMethod, bool $headMode): bool
    {
        foreach ($this->routes as $route) {
            // 方法不匹配则跳过
            if (!in_array($requestMethod, $route->methods, true)) {
                continue;
            }

            // 特殊路由（直接回调）
            if (is_string($route->fn) && $route->fn[0] === '/') {
                if ($route->fn === $requestUrl || $route->fn === '*') {
                    $this->params = [];
                    $route->invoke($this->params);
                    return true;
                }
                continue;
            }

            // 模式匹配
            if ($route->matchPattern($requestUrl)) {
                $this->params = $route->params;
                $route->invoke($this->params);
                return true;
            }
        }

        return false;
    }

    // ───────────────────── 路由缓存 ─────────────────────

    /**
     * 设置路由缓存驱动（推荐方式，支持 File / Redis / Memcache）
     *
     * ```php
     * // 文件缓存
     * Router::setCacheDriver(new FileCache(['cacheDir' => VAR_PATH . '/cache']));
     *
     * // Redis
     * Router::setCacheDriver(new RedisCache(['host' => '127.0.0.1', 'port' => 6379]));
     *
     * // Memcached
     * Router::setCacheDriver(new MemcacheCache(['driver' => 'memcached']));
     * ```
     *
     * @param CacheInterface $driver
     */
    public static function setCacheDriver(CacheInterface $driver): void
    {
        static::$cacheDriver = $driver;

        // 自动检测驱动类型
        if ($driver instanceof \zap\cache\RedisCache) {
            static::$cacheDriverType = 'redis';
        } elseif ($driver instanceof \zap\cache\MemcacheCache) {
            static::$cacheDriverType = $driver->getDriver() ?? 'memcached';
        } else {
            static::$cacheDriverType = 'file';
        }
    }

    /**
     * 设置路由缓存目录（文件缓存快捷方式）
     *
     * @param string $path 缓存目录绝对路径（如 VAR_PATH . '/cache'）
     */
    public static function setCachePath(string $path): void
    {
        static::setCacheDriver(new FileCache(['cacheDir' => $path]));
    }

    /**
     * 获取当前缓存驱动
     *
     * 优先级：
     *   1. 手动 setCacheDriver() / setCachePath() 指定的驱动
     *   2. 自动从 config/cache.php 读取 default 配置并创建对应驱动
     *
     * config/cache.php 示例：
     *   'default' => 'redis',   // 或 'file'、'memcached'、'memcache'
     *   'redis'   => ['params' => ['127.0.0.1', 6379]],
     *   'file'    => ['path'   => VAR_PATH . '/cache'],
     *   'memcached' => ['driver' => 'memcached', 'servers' => [['host' => '127.0.0.1', 'port' => 11211]]],
     *   'status'  => 'enabled',
     */
    protected static function getCacheDriver(): CacheInterface
    {
        if (static::$cacheDriver !== null) {
            return static::$cacheDriver;
        }

        // 自动从 config/cache.php 创建驱动
        $driver = config('cache.default', 'file');

        switch ($driver) {
            case 'redis':
                static::setCacheDriver(new RedisCache(config('cache.redis')));
                break;
            case 'memcached':
            case 'memcache':
                $options = config("cache.{$driver}", []);
                $options['driver'] = $driver;
                static::setCacheDriver(new MemcacheCache($options));
                break;
            case 'file':
            default:
                $cacheDir = config('cache.file.path', var_path('cache'));
                if (!is_dir($cacheDir)) {
                    mkdir($cacheDir, 0755, true);
                }
                static::setCacheDriver(new FileCache([
                    'cacheDir' => $cacheDir,
                    'isCache'  => config('cache.status', 'enabled'),
                ]));
                break;
        }

        return static::$cacheDriver;
    }

    /**
     * 计算缓存校验 hash（基于路由文件修改时间）
     *
     * @param string|null $routeFile
     * @param string      ...$extraFiles
     * @return string
     */
    protected function computeCacheHash(?string $routeFile, string ...$extraFiles): string
    {
        $hashSource = (string)count($this->routes);
        $checkFiles = array_filter([$routeFile, ...$extraFiles], 'is_string');
        foreach ($checkFiles as $file) {
            if (is_file($file)) {
                $hashSource .= $file . '-' . filemtime($file);
            }
        }
        return md5($hashSource);
    }

    /**
     * 将所有已注册路由编译并写入缓存
     *
     * @param string|null $routeFile 路由定义文件路径（用于计算缓存有效性 hash）
     * @param string      ...$extraFiles 其他依赖文件的路径
     * @return bool
     * @throws \RuntimeException 当路由中包含闭包回调时
     */
    public function cacheRoutes(?string $routeFile = null, string ...$extraFiles): bool
    {
        $cacheData = [
            'routes'       => [],
            'named_routes' => static::$namedRoutes,
            'hash'         => '',
        ];

        // 序列化所有路由
        foreach ($this->routes as $index => $route) {
            $cacheData['routes'][] = $route->toCacheData();
        }

        // 计算 hash
        $cacheData['hash'] = $this->computeCacheHash($routeFile, ...$extraFiles);

        // 写入缓存
        $driver = static::getCacheDriver();
        return $driver->set(static::$cacheKey, $cacheData, 0);
    }

    /**
     * 尝试从缓存加载路由
     *
     * 如果缓存有效则直接还原路由表，跳过路由注册流程。
     * 返回 true 表示缓存命中，无需重复注册路由。
     *
     * @param string|null $routeFile 路由文件路径（用于校验缓存有效性）
     * @param string      ...$extraFiles
     * @return bool true=缓存命中, false=缓存过期或不存在
     */
    public function loadRoutesFromCache(?string $routeFile = null, string ...$extraFiles): bool
    {
        $driver = static::getCacheDriver();
        $cacheData = $driver->get(static::$cacheKey);

        // 新格式缓存未命中 → 尝试旧 .php 格式兼容
        if ($cacheData === null || $cacheData === false) {
            if (static::$cacheDriverType === 'file') {
                return $this->loadRoutesFromFileCache($routeFile, ...$extraFiles);
            }
            return false;
        }

        // 校验数据结构
        if (!is_array($cacheData) || !isset($cacheData['hash'], $cacheData['routes'])) {
            return false;
        }

        // 校验 hash（检测路由文件是否变更）
        $hashSource = (string)count($cacheData['routes']);
        $checkFiles = array_filter([$routeFile, ...$extraFiles], 'is_string');
        foreach ($checkFiles as $file) {
            if (is_file($file)) {
                $hashSource .= $file . '-' . filemtime($file);
            }
        }
        if (md5($hashSource) !== $cacheData['hash']) {
            return false; // 缓存过期
        }

        // 还原路由
        $this->routes = [];
        foreach ($cacheData['routes'] as $data) {
            $this->routes[] = Route::fromCacheData($data);
        }

        // 还原命名路由
        if (!empty($cacheData['named_routes'])) {
            static::$namedRoutes = $cacheData['named_routes'];
        }

        return true;
    }

    /**
     * 从文件缓存加载（兼容旧 var_export 格式 .php 缓存文件）
     *
     * @param string|null $routeFile
     * @param string      ...$extraFiles
     * @return bool
     */
    protected function loadRoutesFromFileCache(?string $routeFile, string ...$extraFiles): bool
    {
        $oldPath = (defined('VAR_PATH') ? VAR_PATH . '/cache' : sys_get_temp_dir())
            . DIRECTORY_SEPARATOR . 'routes.cache.php';

        if (!is_file($oldPath)) {
            return false;
        }

        $cacheData = include $oldPath;
        if (!is_array($cacheData) || !isset($cacheData['hash'], $cacheData['routes'])) {
            return false;
        }

        // 校验 hash
        $hashSource = (string)count($cacheData['routes']);
        $checkFiles = array_filter([$routeFile, ...$extraFiles], 'is_string');
        foreach ($checkFiles as $file) {
            if (is_file($file)) {
                $hashSource .= $file . '-' . filemtime($file);
            }
        }
        if (md5($hashSource) !== $cacheData['hash']) {
            return false;
        }

        $this->routes = [];
        foreach ($cacheData['routes'] as $data) {
            $this->routes[] = Route::fromCacheData($data);
        }
        if (!empty($cacheData['named_routes'])) {
            static::$namedRoutes = $cacheData['named_routes'];
        }

        return true;
    }

    /**
     * 删除路由缓存
     */
    public static function clearRouteCache(): bool
    {
        // 清理新驱动缓存
        if (static::$cacheDriver !== null) {
            static::$cacheDriver->delete(static::$cacheKey);
        }

        // 同时清理旧格式文件缓存
        $oldPaths = [
            (defined('VAR_PATH') ? VAR_PATH . '/cache' : sys_get_temp_dir()) . DIRECTORY_SEPARATOR . 'routes.cache.php',
        ];
        foreach ($oldPaths as $oldPath) {
            if (is_file($oldPath)) {
                unlink($oldPath);
            }
        }

        return true;
    }

    /**
     * 获取缓存状态信息
     *
     * @return array{driver: string, cache_key: string, cached: bool, routes_count: int|null}
     */
    public static function getCacheInfo(): array
    {
        $info = [
            'driver'        => static::$cacheDriverType,
            'cache_key'     => static::$cacheKey,
            'cached'        => false,
            'routes_count'  => null,
        ];

        if (static::$cacheDriver === null) {
            return $info;
        }

        $cacheData = static::$cacheDriver->get(static::$cacheKey);

        if ($cacheData === null || $cacheData === false || !is_array($cacheData)) {
            // 尝试旧格式
            $oldPath = (defined('VAR_PATH') ? VAR_PATH . '/cache' : sys_get_temp_dir())
                . DIRECTORY_SEPARATOR . 'routes.cache.php';
            if (is_file($oldPath)) {
                $cacheData = include $oldPath;
                if (is_array($cacheData)) {
                    $info['cached']       = true;
                    $info['routes_count'] = is_array($cacheData) ? count($cacheData['routes'] ?? []) : 0;
                    $info['driver']       = 'file (legacy)';
                    return $info;
                }
            }
            return $info;
        }

        $info['cached']       = true;
        $info['routes_count'] = is_array($cacheData) ? count($cacheData['routes'] ?? []) : 0;
        return $info;
    }

    /**
     * 设置缓存 key（有特殊需要时使用，默认 'zap.routes.cache'）
     */
    public static function setCacheKey(string $key): void
    {
        static::$cacheKey = $key;
    }

    // ───────────────────── 兼容旧版 CMS 方法 ─────────────────────

    /**
     * 获取当前请求路径
     */
    public function getRequestPath(): string
    {
        return $this->requestPath ?? ($_SERVER['REQUEST_URI'] ?? '/');
    }

    /**
     * 触发 404 响应
     */
    public function trigger404(): void
    {
        if ($this->_notfound) {
            $handler = $this->_notfound;
            if ($handler instanceof \Closure) {
                ($handler)();
            } elseif (is_string($handler) && strpos($handler, '@') !== false) {
                [$class, $method] = explode('@', $handler, 2);
                (new $class())->$method();
            } elseif (is_callable($handler)) {
                call_user_func_array($handler, []);
            }
        } else {
            http_response_code(404);
            echo '<h1>404 Not Found</h1>';
        }
        exit;
    }

    /**
     * 将 URL 路径段转换为控制器类名
     * 例如: 'node' → 'Node', 'catalog' → 'Catalog', 'page' => 'Page'
     */
    public static function convertToName(string $name): string
    {
        // 将 slug 转换为 PascalCase 类名，例如 'user-profile' → 'UserProfile'
        $name = str_replace(['-', '_'], ' ', $name);
        $name = ucwords($name);
        return str_replace(' ', '', $name);
    }
}

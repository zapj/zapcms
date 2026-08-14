<?php

declare(strict_types=1);

namespace zap;

use ArrayObject;
use Exception;
use ReflectionClass;
use ReflectionNamedType;
use zap\http\Router;
use zap\util\Arr;

define('ZAP_SRC', realpath(__DIR__));

class App implements \ArrayAccess
{
    public const VERSION = '1.0.6';

    protected string $rootPath;

    protected string $basePath;

    protected string $baseUrl = '';

    /** @var array<string, \Monolog\Logger|\zap\log\SimpleLogger> */
    protected array $logger = [];

    protected static App $instance;

    protected static ArrayObject $container;

    public function __construct(string $basePath)
    {
        $basePath = rtrim(str_replace('\\', '/', $basePath), '/');
        $this->basePath = $basePath . '/';

        $docRoot = Arr::get($_SERVER, 'DOCUMENT_ROOT', $basePath);
        $this->rootPath = rtrim(str_replace('\\', '/', (string)$docRoot), '/') . '/';

        static::$instance = $this;

        if (config('config.debug', false)) {
            error_reporting(E_ALL ^ E_NOTICE);
        } else {
            error_reporting(0);
        }

        ErrorHandler::register();
        $this->prepare();
    }

    /**
     * 获取 App 单例，自动推断项目根路径
     */
    public static function instance(): App
    {
        if (!isset(static::$instance)) {
            $root = realpath(dirname(__DIR__, 3)) ?: dirname(__DIR__, 3);
            static::$instance = new App($root);
        }

        return static::$instance;
    }

    /**
     * 获取框架版本号
     */
    public static function version(): string
    {
        return static::VERSION;
    }

    // ========== 路径方法 ==========

    public function rootPath(string $path = ''): string
    {
        return $this->rootPath . ltrim($path, '/');
    }

    public function basePath(string $path = ''): string
    {
        return $this->basePath . ltrim($path, '/');
    }

    public function configPath(string $filename = ''): string
    {
        return $this->basePath . 'config/' . ltrim($filename, '/');
    }

    public function assetsPath(string $filename = ''): string
    {
        return $this->basePath . 'assets/' . ltrim($filename, '/');
    }

    public function storagePath(string $filename = ''): string
    {
        return $this->basePath . 'storage/' . ltrim($filename, '/');
    }

    public function resourcesPath(string $filename = ''): string
    {
        return $this->basePath . 'resources/' . ltrim($filename, '/');
    }

    public function themesPath(string $filename = ''): string
    {
        return $this->basePath . 'themes/' . ltrim($filename, '/');
    }

    public function varPath(string $filename = ''): string
    {
        return $this->basePath . 'var/' . ltrim($filename, '/');
    }

    /**
     * 获取 public 目录路径（Web 入口）
     */
    public function publicPath(string $filename = ''): string
    {
        return $this->basePath . 'public/' . ltrim($filename, '/');
    }

    // ========== URL 方法 ==========

    public function baseUrl(?string $path = null): string
    {
        if ($path === null) {
            return $this->baseUrl;
        }
        return $this->baseUrl . $path;
    }

    public function themesUrl(?string $url = null): string
    {
        if ($url === null) {
            return $this->baseUrl . '/themes/';
        }
        return $this->baseUrl . '/themes/' . ltrim($url, '/');
    }

    // ========== 环境判断 ==========

    public function isWin(): bool
    {
        return DIRECTORY_SEPARATOR === '\\';
    }

    public function isConsole(): bool
    {
        return \PHP_SAPI === 'cli';
    }

    /**
     * 判断是否为生产环境（debug 关闭）
     */
    public function isProduction(): bool
    {
        return !config('config.debug', false);
    }

    // ========== 初始化 ==========

    protected function prepare(): void
    {
        if ($this->isConsole()) {
            $this->baseUrl = '';
        } else {
            $parts = array_slice(explode('/', $_SERVER['SCRIPT_NAME']), 0, -1);
            $this->baseUrl = implode('/', $parts) ?: '';
        }

        static::$container = new ArrayObject([], ArrayObject::ARRAY_AS_PROPS);

        define('ROOT_PATH', $this->rootPath);
        define('BASE_PATH', $this->basePath);
    }

    // ========== 路由 ==========

    /**
     * 创建路由器实例并注册到容器
     */
    public function createRouter(): Router
    {
        $this->router = new Router();
        return $this->router;
    }

    /**
     * 运行应用（启动路由分发）
     *
     * URI 和 HTTP Method 从 $_SERVER 自动检测，也可通过参数动态传入。
     *
     * @param string|null $uri    请求 URI，不传则从 $_SERVER['REQUEST_URI'] 自动获取
     * @param string|null $method 请求 Method，不传则从 $_SERVER['REQUEST_METHOD'] 自动获取
     */
    public function run(?string $uri = null, ?string $method = null): bool
    {
        $router = $this->router ?? $this->createRouter();
        $this->dispatchRoutes($router);
        return $router->dispatch(
            $uri ?? $_SERVER['REQUEST_URI'] ?? '/',
            $method ?? $_SERVER['REQUEST_METHOD'] ?? 'GET'
        );
    }

    /**
     * 加载路由配置文件
     */
    protected function dispatchRoutes(Router $router): void
    {
        $routeFile = $this->basePath . 'config/route.php';
        if (file_exists($routeFile)) {
            // 路由文件可以通过 $router 变量访问路由器
            require $routeFile;
        }
    }

    // ========== Logger ==========

    /**
     * 获取日志记录器
     *
     * @param string $name 通道名，默认使用 log.default 配置
     * @return \Monolog\Logger|\zap\log\SimpleLogger
     * @throws Exception
     */
    public function getLogger(string $name = 'app')
    {
        $name = config('log.default', $name);

        if (isset($this->logger[$name])) {
            return $this->logger[$name];
        }

        if (!class_exists('\Monolog\Logger')) {
            return $this->logger[$name] = new \zap\log\SimpleLogger($name);
        }

        $handlerClass = config("log.{$name}.handler");
        if (empty($handlerClass)) {
            throw new Exception("Logger handler not configured for channel [{$name}]");
        }

        try {
            $this->logger[$name] = new \Monolog\Logger($name);
            $params = config("log.{$name}.params", []);

            $class = new ReflectionClass($handlerClass);
            if (!$class->isSubclassOf('\Monolog\Handler\HandlerInterface')
                && $handlerClass !== '\Monolog\Handler\HandlerInterface') {
                throw new Exception(
                    "[{$handlerClass}] must implement \\Monolog\\Handler\\HandlerInterface"
                );
            }

            $handler = $class->newInstanceArgs($params);
            $this->logger[$name]->pushHandler($handler);
        } catch (\ReflectionException $e) {
            throw new Exception("Logger handler class not found [{$handlerClass}]", 0, $e);
        }

        return $this->logger[$name];
    }

    // ========== IoC 容器方法 ==========

    /**
     * 从容器中解析或创建对象（PHP 8 反射 API）
     *
     * @param string      $class 完整类名
     * @param array       $args  构造参数（按名称索引）
     * @param string|null $alias 容器别名
     * @return object
     * @throws Exception
     */
    public function make(string $class, array $args = [], ?string $alias = null): object
    {
        try {
            $reflection  = new ReflectionClass($class);
            $constructor = $reflection->getConstructor();

            if ($constructor && $constructor->getNumberOfParameters() > 0) {
                $resolvedArgs = [];
                foreach ($constructor->getParameters() as $param) {
                    $paramName = $param->getName();

                    // 已提供参数
                    if (array_key_exists($paramName, $args)) {
                        $resolvedArgs[$paramName] = $args[$paramName];
                        continue;
                    }

                    // 尝试按类型提示从容器注入
                    $paramType = $param->getType();
                    if ($paramType instanceof ReflectionNamedType && !$paramType->isBuiltin()) {
                        $typeName = $paramType->getName();
                        $resolved = $this->get($typeName);
                        if ($resolved !== null) {
                            $resolvedArgs[$paramName] = $resolved;
                            continue;
                        }
                    }

                    // 尝试按参数名从容器注入
                    $resolved = $this->get($paramName);
                    if ($resolved !== null) {
                        $resolvedArgs[$paramName] = $resolved;
                        continue;
                    }

                    // 参数可选时跳过
                    if ($param->isOptional()) {
                        continue;
                    }
                }

                $object = $reflection->newInstanceArgs($resolvedArgs);
            } else {
                $object = $reflection->newInstance();
            }

            $this->offsetSet($alias ?? $class, $object);
        } catch (\ReflectionException $e) {
            throw new Exception(
                "App::make Instance initialization failed, Error: {$e->getMessage()}",
                0,
                $e
            );
        }

        return $object;
    }

    // ========== 容器方法 ==========

    public function has(string $name): bool
    {
        return isset(static::$container[$name]);
    }

    public function get(string $name)
    {
        return static::$container[$name] ?? null;
    }

    public function set(string $name, $value): void
    {
        static::$container[$name] = $value;
    }

    // ========== 魔术方法 ==========

    public function __get(string $key)
    {
        return static::$container[$key] ?? null;
    }

    public function __set(string $key, $value): void
    {
        static::$container[$key] = $value;
    }

    public function __isset(string $key): bool
    {
        return isset(static::$container[$key]);
    }

    /**
     * @deprecated 请使用 __isset() 或 has() 代替
     */
    public function __has(string $key): bool
    {
        return $this->__isset($key);
    }

    // ========== ArrayAccess ==========

    public function offsetExists($offset): bool
    {
        return isset(static::$container[$offset]);
    }

    public function offsetGet($offset)
    {
        return static::$container[$offset] ?? null;
    }

    public function offsetSet($offset, $value): void
    {
        static::$container[$offset] = $value;
    }

    public function offsetUnset($offset): void
    {
        unset(static::$container[$offset]);
    }
}

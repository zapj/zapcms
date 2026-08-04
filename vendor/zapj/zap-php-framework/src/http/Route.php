<?php

namespace zap\http;

use zap\http\middleware\IMiddleware;

class Route
{
    /** @var string 路由模式 */
    public string $pattern;

    /** @var mixed 处理器（callable 或 'Controller@method'） */
    public $fn;

    /** @var array 支持的 HTTP 方法 */
    public array $methods = [];

    /** @var array 匹配后提取的参数 */
    public array $params = [];

    /** @var array 中间件类名列表 */
    protected array $middlewares = [];

    /** @var string|null 路由名称 */
    protected ?string $routeName = null;

    /** @var string|null 路由组名 */
    protected ?string $groupName = null;

    /** @var string|null 预编译的正则模式（路由缓存时填充，避免每次请求重新构建） */
    protected ?string $compiledRegex = null;

    public function __construct(string $pattern, $fn, array $methods = ['GET'])
    {
        $this->pattern = $pattern;
        $this->fn      = $fn;
        $this->methods = $methods;
    }

    // ───────────────────── 模式匹配 ─────────────────────

    /**
     * 编译路由模式为正则（结果缓存复用）
     */
    public function compilePattern(): string
    {
        if ($this->compiledRegex !== null) {
            return $this->compiledRegex;
        }

        // 转义 / 并处理 {any} 通配符
        $regex = '/^' . str_replace(['/', '{any}'], ['\/', '([^/]+)'], $this->pattern) . '$/';

        // 替换命名的占位符 {name:pattern}
        $regex = preg_replace('/\{([a-zA-Z_][a-zA-Z0-9_]*):([^}]+)\}/', '(?P<$1>$2)', $regex);

        // 替换简单占位符 {name}
        $regex = preg_replace('/\{([a-zA-Z_][a-zA-Z0-9_]*)\}/', '(?P<$1>[^/]+)', $regex);

        $this->compiledRegex = $regex;
        return $regex;
    }

    /**
     * 尝试将 URL 匹配到路由模式
     */
    public function matchPattern(string $url): bool
    {
        $pattern = $this->compilePattern();

        if (preg_match($pattern, $url, $matches)) {
            // 提取命名参数
            foreach ($matches as $k => $v) {
                if (is_string($k)) {
                    $this->params[$k] = $v;
                }
            }
            return true;
        }

        return false;
    }

    /**
     * 导出为可缓存的数组（排除闭包等不可序列化的回调）
     *
     * @return array
     * @throws \RuntimeException 当处理器是闭包时
     */
    public function toCacheData(): array
    {
        if ($this->fn instanceof \Closure) {
            throw new \RuntimeException(
                "Cannot cache route '{$this->pattern}': closure handlers are not serializable. " .
                "Use 'Controller@method' string syntax instead."
            );
        }

        // 确保正则已编译
        $this->compilePattern();

        return [
            'pattern'       => $this->pattern,
            'fn'            => $this->fn,
            'methods'       => $this->methods,
            'middlewares'   => $this->middlewares,
            'routeName'     => $this->routeName,
            'groupName'     => $this->groupName,
            'compiledRegex' => $this->compiledRegex,
        ];
    }

    /**
     * 从缓存数据还原 Route 实例
     */
    public static function fromCacheData(array $data): self
    {
        $route = new self($data['pattern'], $data['fn'], $data['methods']);
        $route->compiledRegex = $data['compiledRegex'] ?? null;
        $route->middlewares   = $data['middlewares'] ?? [];
        $route->routeName     = $data['routeName'] ?? null;
        $route->groupName     = $data['groupName'] ?? null;
        return $route;
    }

    // ───────────────────── 中间件 ─────────────────────

    /**
     * 添加中间件
     *
     * @param string $middleware 中间件类名
     * @return $this
     */
    public function middleware(string $middleware): self
    {
        $this->middlewares[] = $middleware;
        return $this;
    }

    /**
     * 获取中间件列表
     */
    public function getMiddleware(): array
    {
        return $this->middlewares;
    }

    // ───────────────────── 命名路由 ─────────────────────

    /**
     * 设置路由名称
     *
     * @return $this
     */
    public function name(string $name): self
    {
        $this->routeName = $name;
        Router::name($name, $this->pattern);
        return $this;
    }

    /**
     * 设置路由组名称
     *
     * @return $this
     */
    public function group(string $groupName): self
    {
        $this->groupName = $groupName;
        return $this;
    }

    /**
     * 获取路由名称
     */
    public function getName(): ?string
    {
        return $this->routeName;
    }

    // ───────────────────── 调用 ─────────────────────

    /**
     * 执行路由处理器
     *
     * @param array $params 路由参数
     */
    public function invoke(array $params = []): void
    {
        if (is_object($this->fn) && ($this->fn instanceof \Closure)) {
            // 闭包回调
            $this->runMiddlewaresAndHandler(function () use ($params) {
                return call_user_func_array($this->fn, $params);
            });
            return;
        }

        if (is_string($this->fn) && strpos($this->fn, '@') !== false) {
            // Controller@method 格式
            [$controller, $method] = explode('@', $this->fn);

            $reflectedMethod = new \ReflectionMethod($controller, $method);
            if ($reflectedMethod->isPublic() && !$reflectedMethod->isAbstract()) {
                $this->runMiddlewaresAndHandler(function () use ($controller, $method, $params, $reflectedMethod) {
                    if ($reflectedMethod->isStatic()) {
                        return call_user_func_array([$controller, $method], $params);
                    }
                    $instance = new $controller();
                    $instance->setParams($params);
                    return call_user_func_array([$instance, $method], $params);
                });
            }

            return;
        }

        if (is_callable($this->fn)) {
            // 直接可调用的
            $this->runMiddlewaresAndHandler(function () use ($params) {
                return call_user_func_array($this->fn, $params);
            });
            return;
        }
    }

    /**
     * 运行中间件链，然后执行处理器
     */
    protected function runMiddlewaresAndHandler(callable $handler): void
    {
        if (empty($this->middlewares)) {
            $handler();
            return;
        }

        // 构建中间件链
        $middlewares = $this->middlewares;
        $next = function () use (&$middlewares, &$next, $handler) {
            if (empty($middlewares)) {
                return $handler();
            }
            $middlewareClass = array_shift($middlewares);
            $middleware = new $middlewareClass();
            return $middleware->handle(ZapRequest::instance(), $next);
        };

        $next();
    }
}

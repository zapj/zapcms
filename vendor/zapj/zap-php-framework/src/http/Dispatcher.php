<?php

namespace zap\http;

use \Exception;
use zap\exception\NotFoundException;
use zap\view\View;

class Dispatcher
{
    /** @var Router */
    protected $router;

    /**
     * @var string|null URL 前缀（如 /admin），用于解析路由时移除
     */
    protected string $routeBase = '';

    public function __construct($router)
    {
        $this->router = $router;
    }

    /**
     * 设置路由基础路径前缀
     * @param string $routeBase
     */
    public function setRouteBase(string $routeBase): void
    {
        $this->routeBase = rtrim($routeBase, '/');
    }

    /**
     * 处理请求分发
     *
     * @param string|null $requestUrl
     * @return bool true=成功拦截, false=无匹配路由
     */
    public function handle($requestUrl = null): bool
    {
        $handled = false;
        $requestUrl = $this->parseUrlPath($requestUrl);
        $requestUrl = urldecode($requestUrl);
        $requestMethod = $_SERVER['REQUEST_METHOD'] ?? 'GET';

        try {
            // 匹配路由
            if ($this->router->dispatch($requestUrl, $requestMethod)) {
                $handled = true;
            }

            // 无匹配路由 → 执行 notFound 处理
            if (!$handled) {
                $handler = $this->router->getNotFound();
                if (is_callable($handler)) {
                    $result = call_user_func($handler, $requestUrl);
                    if ($result !== null) {
                        echo $result;
                        $handled = true;
                    }
                }
            }
        } catch (NotFoundException $e) {
            // notFound 处理器抛出的，直接展示错误页
            if (config('config.debug', false)) {
                (new \zap\util\Printer($e))->display();
            } else {
                ZView::render(__DIR__ . '/../resources/views/errors/exception.php', ['e' => $e]);
            }
            $handled = true;
        } catch (Exception $e) {
            if (config('config.debug', false)) {
                (new \zap\util\Printer($e))->display();
            } else {
                ZView::render(__DIR__ . '/../resources/views/errors/exception.php', ['e' => $e]);
            }
            $handled = true;
        }

        return $handled;
    }

    /**
     * 解析 URL 路径（剥离基础前缀）
     */
    private function parseUrlPath($uri): string
    {
        $start = 0;
        $uri = (string)$uri;

        // 获取请求 URI
        if ($uri) {
            $queryPos = strpos($uri, '?');
            if ($queryPos !== false) {
                $queryString = substr($uri, $queryPos + 1);
                parse_str($queryString, $queryData);
                $_REQUEST = array_merge($_REQUEST, $queryData);
                $_GET = array_merge($_GET, $queryData);
                $uri = substr($uri, 0, $queryPos);
            }
        } else {
            $uri = $_SERVER['REQUEST_URI'] ?? '';
            $stripos = function_exists('mb_stripos') ? 'mb_stripos' : 'stripos';
            $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';

            // 过滤查询字符串
            $queryPos = strpos($uri, '?');
            if ($queryPos !== false) {
                parse_str(substr($uri, $queryPos + 1), $queryData);
                $_REQUEST = array_merge($_REQUEST, $queryData);
                $_GET = array_merge($_GET, $queryData);
                $uri = substr($uri, 0, $queryPos);
            }

            // 去除脚本名
            if ($stripos($uri, $scriptName) === 0) {
                $uri = substr($uri, strlen($scriptName));
            }

            // 去除 index.php
            if ($uri && stripos($uri, 'index.php') === 0) {
                $uri = substr($uri, 9);
            }
        }

        // 安全地剥离路由前缀
        if ($this->routeBase && strpos($uri, $this->routeBase) === 0) {
            $uri = substr($uri, strlen($this->routeBase));
        }

        // 补上前导 /
        if ($uri === '' || $uri === false || $uri[0] !== '/') {
            $uri = '/' . ltrim($uri, '/');
        }

        // 去除末尾斜杠（保留根路径 '/'）
        if ($uri !== '/' && $uri !== false) {
            $uri = rtrim($uri, '/');
        }

        return $uri;
    }
}

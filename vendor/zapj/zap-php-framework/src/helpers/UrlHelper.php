<?php

namespace zap\helpers;

use zap\facades\Router;

class UrlHelper
{
    /** @var string Base URL of the application */
    protected $baseUrl = '';

    /** @var string Current request URI */
    protected $current = '';

    /** @var array Cached named routes */
    protected $namedRoutes = [];

    /**
     * Set or get the base URL.
     *
     * @param string|null $url If null, returns current base URL
     */
    public function base($url = null)
    {
        if ($url !== null) {
            $this->baseUrl = rtrim($url, '/');
            return $this;
        }
        if ($this->baseUrl) {
            return $this->baseUrl;
        }
        // Auto-detect from request
        $scheme = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http';
        $host   = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $this->baseUrl = $scheme . '://' . $host;
        return $this->baseUrl;
    }

    /**
     * Generate the URL for the application home page.
     */
    public function home(): string
    {
        $base = $this->base();
        // When in admin panel, return admin dashboard URL
        if (defined('IN_ZAP_ADMIN') && IN_ZAP_ADMIN) {
            $prefix = defined('Z_ADMIN_PREFIX') ? trim(Z_ADMIN_PREFIX, '/') : 'z-admin';
            return $base . '/' . $prefix;
        }
        return $base;
    }

    /**
     * Get the current full URL (with scheme & host).
     */
    public function full(): string
    {
        return $this->base() . ($_SERVER['REQUEST_URI'] ?? '/');
    }

    /**
     * Get the previous URL from the Referer header.
     */
    public function previous(): string
    {
        return $_SERVER['HTTP_REFERER'] ?? $this->base();
    }

    /**
     * Generate a URL to a named route.
     *
     * @param string $name   Route name
     * @param array  $params Route parameters
     * @param bool   $absolute Whether to include base URL
     * @return string
     */
    public function route(string $name, array $params = [], bool $absolute = false): string
    {
        $namedRoutes = $this->getNamedRoutes();

        if (!isset($namedRoutes[$name])) {
            // Fallback: treat $name as a path pattern
            return $this->appendSuffix($this->buildPath($name, $params, $absolute));
        }

        $pattern = $namedRoutes[$name]['pattern'] ?? '';
        $uri     = $this->replaceRouteParams($pattern, $params);

        // Append remaining params as query string
        if (!empty($params)) {
            $query = http_build_query($params);
            $uri .= (str_contains($uri, '?') ? '&' : '?') . $query;
        }

        $uri = '/' . ltrim($uri, '/');
        $uri = $this->appendSuffix($uri);

        return $absolute ? rtrim($this->base(), '/') . $uri : $uri;
    }

    /**
     * Get the current URL path (relative).
     */
    public function current(): string
    {
        return parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
    }

    /**
     * Get the current URL path with query string (relative).
     */
    public function currentFull(): string
    {
        return $_SERVER['REQUEST_URI'] ?? '/';
    }

    /**
     * Get the current controller name.
     * Priority: router property → URL parsing (for admin with Z_ADMIN_PREFIX)
     */
    public function controller(): string
    {
        // Try router property first (if framework sets it)
        try {
            if (!empty(app()->router->controller)) {
                return app()->router->controller;
            }
        } catch (\Throwable $e) {}

        // Derive from current URL
        $path = trim(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/', '/');
        if (defined('IN_ZAP_ADMIN') && IN_ZAP_ADMIN) {
            $prefix = defined('Z_ADMIN_PREFIX') ? trim(Z_ADMIN_PREFIX, '/') : 'z-admin';
            if (str_starts_with($path, $prefix . '/')) {
                $path = substr($path, strlen($prefix) + 1);
            } elseif ($path === $prefix) {
                $path = '';
            }
        }
        $segments = $path !== '' ? explode('/', $path) : [];
        return $segments[0] ?? 'index';
    }

    /**
     * Get the current method/action name.
     * Priority: router property → URL parsing (for admin with Z_ADMIN_PREFIX)
     */
    public function method(): string
    {
        try {
            if (!empty(app()->router->method)) {
                return app()->router->method;
            }
        } catch (\Throwable $e) {}

        $path = trim(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/', '/');
        if (defined('IN_ZAP_ADMIN') && IN_ZAP_ADMIN) {
            $prefix = defined('Z_ADMIN_PREFIX') ? trim(Z_ADMIN_PREFIX, '/') : 'z-admin';
            if (str_starts_with($path, $prefix . '/')) {
                $path = substr($path, strlen($prefix) + 1);
            } elseif ($path === $prefix) {
                $path = '';
            }
        }
        $segments = $path !== '' ? explode('/', $path) : [];
        return $segments[1] ?? 'index';
    }

    /**
     * Generate a URL for a controller action.
     *
     * @param string $action      "Controller@Action" format
     * @param array  $queryParams Query string parameters
     * @param array  $pathParams  Path parameters for the URL pattern
     * @return string
     */
    public function action(string $action, $queryParams = [], $pathParams = []): string
    {
        $queryParams = is_array($queryParams) ? $queryParams : [];
        $pathParams = is_array($pathParams) ? $pathParams : [];
        

        [$controller,$action] = explode('@',$action);
        $controller = strtolower(trim(preg_replace('/([A-Z])/', '-$1', $controller),'-'));
        $action = strtolower(trim(preg_replace('/([A-Z])/', '-$1', $action),'-'));
        $uri = '';
        if($action){
            $uri .= '/' . $controller . '/' . $action;
        } else if($controller){
            $uri .= '/' . $controller;
        }

        // Admin context: prepend admin prefix
        if (defined('IN_ZAP_ADMIN') && IN_ZAP_ADMIN) {
            $uri = Z_ADMIN_PREFIX.'/'. ltrim($uri, '/');
        }else{
             $uri = '/' . ltrim($uri, '/');
        }
   
        // Replace path params in the action string
        foreach ($pathParams as $key => $value) {
            $uri = str_replace('{' . $key . '}', urlencode($value), $uri);
        }

        if (!empty($queryParams)) {
            $uri .= (str_contains($uri, '?') ? '&' : '?') . http_build_query($queryParams);
        }

        return $this->appendSuffix($uri);
    }

    /**
     * Build a URL from a format string with parameters.
     *
     * @param string $format      URL format (e.g. "/user/{id}/edit")
     * @param array  $params      Path parameters
     * @param bool   $asQueryString If true, append remaining params as query string
     * @return string
     */
    public function to(string $format, array $params = [], bool $asQueryString = true): string
    {
        // Replace named placeholders
        foreach ($params as $key => $value) {
            $placeholder = '{' . $key . '}';
            if (str_contains($format, $placeholder)) {
                $format = str_replace($placeholder, urlencode($value), $format);
                unset($params[$key]);
            }
        }

        $uri = '/' . ltrim($format, '/');

        // Append remaining params as query string or positional path segments
        if (!empty($params)) {
            if ($asQueryString) {
                $uri .= (str_contains($uri, '?') ? '&' : '?') . http_build_query($params);
            } else {
                $uri .= '/' . implode('/', array_map('urlencode', $params));
            }
        }

        return $this->appendSuffix($uri);
    }

    /**
     * Check if the current URL matches a given action.
     *
     * @param string      $action Action to check against
     * @param string|null $activeClass Class to return/echo if active (null returns boolean)
     * @return bool|string|null
     */
    public function isActive(string $action, ?string $activeClass = null)
    {
        $current = $this->current();
        $active  = $this->urlMatch($current, $action);

        if ($activeClass !== null) {
            return $active ? $activeClass : '';
        }

        return $active;
    }

    /**
     * Check if a menu item is active.
     *
     * If $action looks like an active_rule pattern (e.g. "User", "User/.*", "Node/list"),
     * it matches against current controller/method with regex support.
     * If it looks like a URL path (starts with "/" or "http"), it matches against current URL.
     *
     * @param string|array $action Action/rule to check, or array of rules
     * @param string|null  $output Class or text for active state (echoed if matched)
     * @return bool
     */
    public function active($action, $output = null): bool
    {
        if (empty($action)) {
            return false;
        }

        // Get current controller/method (e.g. "user", "user/list")
        $controller = $this->controller();
        $method     = $this->method();
        $currentAction = strtolower($controller . ($method && $method !== 'index' ? '/' . $method : ''));

        $matched = false;

        if (is_string($action)) {
            // Is it a URL path? (/xxx, http://, etc.)
            $isUrl = (str_starts_with($action, '/') || str_starts_with($action, 'http'));

            if ($isUrl) {
                // URL matching
                $matched = ($action === $this->current() || $this->urlMatch($this->current(), $action));
            } else {
                // active_rule matching: regex against controller/method
                // e.g. "User/.*" → matches user/list, user/edit, user/anything
                $matched = ($action === $currentAction || @preg_match("#^{$action}$#i", $currentAction));
            }
        } elseif (is_array($action)) {
            $matched = in_array($currentAction, (array)$action);
        }

        if ($matched && $output !== null) {
            echo $output;
        }

        return $matched;
    }

    /**
     * Generate a secure (https) URL.
     *
     * @param string $path
     * @return string
     */
    public function secure(string $path = ''): string
    {
        $base  = preg_replace('/^https?/', 'https', $this->base());
        $path  = '/' . ltrim($path, '/');
        return rtrim($base, '/') . $path;
    }

    /**
     * Get route data for the given route name.
     *
     * @param string|null $name Route name, or null for all route data
     * @return array|null
     */
    public function getRouteData($name = null)
    {
        if ($name !== null) {
            $namedRoutes = $this->getNamedRoutes();
            return $namedRoutes[$name] ?? null;
        }

        try {
            return [
                'controller' => $this->controller(),
                'method'     => $this->method(),
                'uri'        => $this->current(),
                'full'       => $this->full(),
            ];
        } catch (\Throwable $e) {
            return null;
        }
    }

    // ─── Internals ─────────────────────────────────────────────

    /**
     * Retry named routes from the router.
     */
    protected function getNamedRoutes(): array
    {
        if (!empty($this->namedRoutes)) {
            return $this->namedRoutes;
        }

        try {
            $routes = app()->router->namedRoutes ?? app()->router->routes ?? [];
            $this->namedRoutes = (array)$routes;
        } catch (\Throwable $e) {
            $this->namedRoutes = [];
        }

        return $this->namedRoutes;
    }

    /**
     * Replace route parameters in a pattern.
     */
    protected function replaceRouteParams(string $pattern, array &$params): string
    {
        if (empty($params)) {
            return $pattern;
        }

        // Replace named parameters {param}
        $pattern = preg_replace_callback('/\{(\w+)\??\}/', function ($matches) use (&$params) {
            $key = $matches[1];
            if (isset($params[$key])) {
                $value = urlencode($params[$key]);
                unset($params[$key]);
                return $value;
            }
            // Optional param
            if (str_ends_with($matches[0], '?}')) {
                return '';
            }
            return $matches[0];
        }, $pattern);

        return $pattern;
    }

    /**
     * Build a path from a format string, replacing positional or named params.
     */
    protected function buildPath(string $format, array $params, bool $absolute): string
    {
        foreach ($params as $key => $value) {
            $placeholder = '{' . $key . '}';
            if (str_contains($format, $placeholder)) {
                $format = str_replace($placeholder, urlencode($value), $format);
                unset($params[$key]);
            }
        }

        $uri = '/' . ltrim($format, '/');

        if (!empty($params)) {
            $uri .= (str_contains($uri, '?') ? '&' : '?') . http_build_query($params);
        }

        return $absolute ? rtrim($this->base(), '/') . $uri : $uri;
    }

    /**
     * Check if a URL path matches a given action pattern.
     */
    protected function urlMatch(string $current, string $action): bool
    {
        if ($current === $action) {
            return true;
        }

        // Support wildcard or partial matching
        if (str_ends_with($action, '*')) {
            $prefix = rtrim($action, '*');
            return $prefix === '' || str_starts_with($current, $prefix);
        }

        return false;
    }

    /**
     * Quote slashes for regex pattern matching.
     */
    protected function quoteSlash(string $str): string
    {
        return str_replace('/', '\/', $str);
    }

    /**
     * 根据配置自动追加 URL 后缀（如 .html）
     */
    protected function appendSuffix(string $url): string
    {
        $suffix = config('config.suffix', '');
        if (empty($suffix)) {
            return $url;
        }

        // 处理绝对 URL（含协议头）
        $scheme = '';
        if (preg_match('#^(https?://[^/]+)(/.*)$#', $url, $m)) {
            $scheme = $m[1];
            $url    = $m[2];
        }

        // 分离路径和查询参数
        $queryPos = strpos($url, '?');
        $path = $queryPos !== false ? substr($url, 0, $queryPos) : $url;
        $query = $queryPos !== false ? substr($url, $queryPos) : '';

        // 已以 suffix 结尾则跳过
        if (str_ends_with($path, $suffix)) {
            return $scheme . $url;
        }

        // 路径为空或已是根路径则不追加
        $path = rtrim($path, '/');
        if ($path === '') {
            $path = '/';
        } else {
            $path .= $suffix;
        }

        return $scheme . $path . $query;
    }
}

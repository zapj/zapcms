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
        return $this->base();
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
            return $this->buildPath($name, $params, $absolute);
        }

        $pattern = $namedRoutes[$name]['pattern'] ?? '';
        $uri     = $this->replaceRouteParams($pattern, $params);

        // Append remaining params as query string
        if (!empty($params)) {
            $query = http_build_query($params);
            $uri .= (str_contains($uri, '?') ? '&' : '?') . $query;
        }

        return $absolute ? rtrim($this->base(), '/') . '/' . ltrim($uri, '/') : '/' . ltrim($uri, '/');
    }

    /**
     * Get the current URL path (relative).
     */
    public function current(): string
    {
        return parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
    }

    /**
     * Get the current controller name.
     */
    public function controller(): string
    {
        try {
            return app()->router->controller ?? '';
        } catch (\Throwable $e) {
            return '';
        }
    }

    /**
     * Get the current method/action name.
     */
    public function method(): string
    {
        try {
            return app()->router->method ?? '';
        } catch (\Throwable $e) {
            return '';
        }
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
        
        $uri = $action;

        // Admin context: prepend admin prefix and convert "Controller@method" to "controller/method"
        if (defined('IN_ZAP_ADMIN') && IN_ZAP_ADMIN) {
            $prefix = defined('Z_ADMIN_PREFIX') ? trim(Z_ADMIN_PREFIX, '/') : 'z-admin';
            $uri = str_replace('@', '/', $uri);
            $uri = $prefix . '/' . ltrim($uri, '/');
        }

        // Replace path params in the action string
        foreach ($pathParams as $key => $value) {
            $uri = str_replace('{' . $key . '}', urlencode($value), $uri);
        }

        $uri = '/' . ltrim($uri, '/');

        if (!empty($queryParams)) {
            $uri .= (str_contains($uri, '?') ? '&' : '?') . http_build_query($queryParams);
        }

        return $uri;
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

        return $uri;
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
     * Legacy active method (may echo output).
     *
     * @param string      $action Action to check
     * @param string|null $output Class or text for active state
     * @return bool
     */
    public function active(string $action, $output = null): bool
    {
        $active = $this->urlMatch($this->current(), $action);

        if ($active && $output !== null) {
            echo $output;
        }

        return $active;
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
}

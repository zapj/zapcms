<?php

namespace zap\http;

/**
 * @method static string method()
 * @method static bool   isPost()
 * @method static bool   isGet()
 * @method static bool   isPut()
 * @method static bool   isPatch()
 * @method static bool   isDelete()
 * @method static bool   isOptions()
 * @method static bool   isHead()
 * @method static bool   isAjax()
 * @method static bool   isJson()
 * @method static bool   isMethod(string $method)
 * @method static string url()
 * @method static string fullUrl()
 * @method static string uri()
 * @method static string path()
 * @method static string scheme()
 * @method static string host()
 * @method static int    port()
 * @method static mixed  query($key = null, $default = null)
 * @method static mixed  post($key = null, $default = null)
 * @method static mixed  input($key = null, $default = null)
 * @method static bool   has(string $key)
 * @method static array  only(array $keys)
 * @method static array  except(array $keys)
 * @method static array  all()
 * @method static array|null json()
 * @method static string rawBody()
 * @method static string ip()
 * @method static string userAgent()
 * @method static string referer()
 * @method static string language()
 * @method static mixed  headers($key = null, $default = null)
 * @method static array|null file($key = null)
 * @method static bool   hasFile(string $key)
 * @method static ZapRequest instance()
 */
class Request
{
    public static function __callStatic($name, $arguments)
    {
        return ZapRequest::instance()->$name(...$arguments);
    }
}

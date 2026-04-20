<?php
namespace zap\http;

use zap\facades\Facade;


/**
 * @method static string ip($default = '')
 * @method static realIp(string $default = '', bool $exclude_reserved = false)
 * @method static string getPreferredLanguage()
 * @method static string|array get(string $name = null, $default = null)
 * @method static string|array post(string $name = null, $default = null)
 * @method static string|array all(string $name = null, $default = null)
 * @method static string protocol()
 * @method static bool isAjax()
 * @method static bool isJson()
 * @method static bool isXml()
 * @method static string prevUrl($default = '')
 * @method static string userAgent($default = '')
 * @method static file($key = null, $default = null)
 * @method static cookie($key = null, $default = null)
 * @method static server($key = null, $default = null)
 * @method static headers($key = null, $default = null)
 * @method static string query_string($default = '')
 * @method static bool isMethod(string $method)
 * @method static bool isPost()
 * @method static bool isGet()
 * @method static string method()
 * @method static string raw()
 * @method static string getScriptName($suffix = '')
 */
class Request extends Facade {

    const NAME = '_zapRequest';

    /**
     * @return ZapRequest
     */
    protected static function getInstance()
    {
        if(!app()->has(self::NAME)){
            app()->set(self::NAME,ZapRequest::instance());
        }

        return app()->get(static::NAME);
    }

}

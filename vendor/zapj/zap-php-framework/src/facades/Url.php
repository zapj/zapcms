<?php

namespace zap\facades;

/**
 * @method static string base($url = null)
 * @method static string home()
 * @method static string current()
 * @method static string action($controller,$queryParams = null,$pathParams = null)
 * @method static bool active($action,$output = null)
 * @method static string to($format,$params = [],$queryString = true)
 * @method static string controller()
 * @method static string method()
 * @method static array getRouteData($name = null)
 */
class Url extends Facade
{

    const NAME = '_urlHelper';


    protected static function getInstance()
    {
        $app = app();
        if(!isset($app[static::NAME])){
            $app[static::NAME] = new \zap\helpers\UrlHelper();
        }

        return $app[static::NAME];
    }

}
<?php

namespace zap\facades;

/**
 * @method static base($url = null)
 * @method static home()
 * @method static current()
 * @method static action($controller,$queryParams = null,$pathParams = null)
 * @method static active($action,$output = null)
 * @method static to($format,$params = [],$queryString = true)
 * @method static controller()
 * @method static method()
 * @method static getRouteData($name = null)
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
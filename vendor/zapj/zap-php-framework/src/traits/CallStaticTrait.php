<?php

namespace zap\traits;

trait CallStaticTrait {

    public static function __callStatic($method, $arguments)
    {
        if(method_exists(static::instance(),$method)){
            return static::instance()->$method(...$arguments);
        }
        return false;
    }


}
<?php

namespace zap\facades;

use RuntimeException;

class Facade
{

    protected static function getInstance()
    {
        return true;
    }

    protected static function instance()
    {
        return static::getInstance();
    }

    public static function __callStatic($method, $args)
    {
        $instance = static::getInstance();
        if (! $instance) {
            throw new RuntimeException('A facade instance has not been set.');
        }
        return $instance->$method(...$args);
    }
}
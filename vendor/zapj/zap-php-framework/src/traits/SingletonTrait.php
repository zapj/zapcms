<?php

namespace zap\traits;

trait SingletonTrait {

    private static $instance;

    protected function __construct() { }

    /**
     * @return static
     */
    public static function instance()
    {
        if (!self::$instance) {
            self::$instance = new self();
            self::$instance->init();
        }

        return self::$instance;
    }

    protected function init(){

    }

}
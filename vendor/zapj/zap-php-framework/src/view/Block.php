<?php

namespace zap\view;

class Block
{
    public static $view;

    public static function begin($name){
        if(!is_null(static::$view)){
            static::$view->beginBlock($name);
        }
    }

    public static function end(){
        if(!is_null(static::$view)){
            static::$view->endBlock();
        }
    }
}
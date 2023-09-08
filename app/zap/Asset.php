<?php

namespace app\zap;



class Asset
{
    public static function css($url, $position = ASSETS_HEAD){
        register_styles($url,$position);
    }

    public static function js($url, $position = ASSETS_HEAD){
        register_scripts($url,$position);
    }

    public static function library($name){
        $name = preg_replace('/[^A-Za-z0-9]/', '', $name);
        $libraryPath = resource_path('/libs/'.$name.'.php');
        if(is_file($libraryPath)){
            require_once $libraryPath;
        }
    }
}
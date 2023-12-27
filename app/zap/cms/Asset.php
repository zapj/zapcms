<?php
/*
 * Copyright (c) 2023.  ZAP.CN  - ZAP CMS - All Rights Reserved
 * @author Allen
 * @email zapcms@zap.cn
 * @date 2023/12/27 上午11:10
 * @lastModified 2023/9/8 下午4:32
 *
 */

namespace zap\cms;


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
<?php
/*
 * Copyright (c) 2023.  ZAP.CN  - ZAP CMS
 */

namespace zap\core;

use zap\Option;

class Theme
{
    function saveThemeOptions($theme){
        $themeFile = themes_path("{$theme}/theme.json");
        $themeInfo = IOUtils::readJsonFile($themeFile,true) ?:[];
        $theme = Option::get('website.theme','basic');
        $themeSettingsKeys = Option::getKeys($theme,'REGEXP');
        foreach ($themeInfo['options'] ?? [] as $key=>$value){
            if(is_object($value) || is_array($value)){
                $value = json_encode($value);
            }
            if(in_array($key, $themeSettingsKeys)){
                Option::update($key,$value);
            }else{
                Option::add($key,$value);
            }

        }
    }

}
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

        if(!empty($themeInfo['settings']['option_keys'])){
            $option_keys = $themeInfo['settings']['option_keys'];
        }else{
            $option_keys[] = [$theme];
        }
        $themeSettingsKeys = Option::getKeys($option_keys,'REGEXP');
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
        if(in_array("{$theme}.option_keys",$themeSettingsKeys)){
            Option::update("{$theme}.option_keys",json_encode($option_keys));
        }else{
            Option::add("{$theme}.option_keys",json_encode($option_keys));
        }
    }

}
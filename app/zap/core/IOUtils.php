<?php
/*
 * Copyright (c) 2023.  ZAP.CN  - ZAP CMS
 */

namespace zap\core;

class IOUtils
{
    public static function readJsonFile($file,?bool $associative = null)
    {
        if(is_file($file)){
            $content = file_get_contents($file);
            $jsonData = json_decode($content,$associative);
            if(json_last_error() === JSON_ERROR_NONE){
                return $jsonData;
            }
        }
        return false;
    }

}
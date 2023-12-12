<?php
/*
 * Copyright (c) 2023.  ZAP.CN  - ZAP CMS
 */

namespace zap\helpers;

use zap\image\Image;
use zap\Log;
use zap\util\Str;

class ThumbHelper
{
    public static function thumb($file,$width,$height): string
    {
        if(Str::startsWith($file,'/storage')){
            $file = str_ireplace('/storage','',$file);
        }
        $file = ltrim($file,'/\\');
        $path_parts = pathinfo($file);
        $path_parts['dirname'] = $path_parts['dirname'] == '.' ? '' : "{$path_parts['dirname']}/";
        $thumb_file = "{$path_parts['dirname']}{$path_parts['filename']}-{$width}x{$height}.{$path_parts['extension']}";
        if(is_file(storage_path("thumbs/".$thumb_file))){
            return base_url("/storage/thumbs/".$thumb_file);
        }
        $file = storage_path($file);
        if(!is_file($file)){
            return base_url("/assets/images/placeholder.jpg");
        }
        $img = Image::from($file);
//        $originalPath = dirname($file);
        $img->thumb($width,$height)->saveFile(storage_path("thumbs/".$thumb_file));
        return base_url("/storage/thumbs/".$thumb_file);
    }


}
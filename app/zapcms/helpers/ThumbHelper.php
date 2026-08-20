<?php
/*
 * Copyright (c) 2023.  ZAP.CN  - ZAP CMS - All Rights Reserved
 * @author Allen
 * @email zapcms@zap.cn
 * @date 2023/12/27 上午11:30
 * @lastModified 2023/11/23 下午4:57
 *
 */

namespace zapcms\helpers;

use zap\image\Image;
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
        $dirname = $path_parts['dirname'] ?? '.';
        $filename = $path_parts['filename'] ?? basename($file, '.' . ($path_parts['extension'] ?? ''));
        $extension = $path_parts['extension'] ?? 'jpg';
        $dirname = $dirname == '.' ? '' : "{$dirname}/";
        $thumb_file = "{$dirname}{$filename}-{$width}x{$height}.{$extension}";
        if(is_file(storage_path("thumbs/".$thumb_file))){
            return storage_url("thumbs/".$thumb_file);
        }
        $file = storage_path($file);
        if(!is_file($file)){
            // 原图不存在：直接返回占位图（保持原名，不生成缩略图副本，避免产生大量重复图片）
            // 为占位图也生成对应尺寸的缩略图
            $placeholder = app()->basePath('/assets/images/placeholder.jpg');
            if(is_file($placeholder)){
                $thumb_file = "placeholder-{$width}x{$height}.jpg";
                if(!is_file(storage_path("thumbs/".$thumb_file))){
                    $img = Image::from($placeholder);
                    $img->thumb($width,$height)->saveFile(storage_path("thumbs/".$thumb_file));
                }
                return storage_url("thumbs/".$thumb_file);
            }
            return base_url("/assets/images/placeholder.jpg");
        }
        $img = Image::from($file);
//        $originalPath = dirname($file);
        $img->thumb($width,$height)->saveFile(storage_path("thumbs/".$thumb_file));
        return storage_url("thumbs/".$thumb_file);
    }


}
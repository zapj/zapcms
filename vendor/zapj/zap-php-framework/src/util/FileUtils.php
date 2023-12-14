<?php

namespace zap\util;

use FilesystemIterator;

class FileUtils {

    public static function copy($from , $to){
        return copy($from, $to);
    }


    public static function write($filename , $content , $flags = 0){
        file_put_contents($filename,$content,$flags);
    }

    public static function writeLines($filename , $content = []){
        $fh = fopen($filename,'w');
        foreach($content as $line){
            fwrite($fh, $line . PHP_EOL);
        }
        fclose($fh);
    }

    public static function sizeOf($filename){
        $size = filesize($filename);
        if($size === false){
            $size = 0;
        }
        return $size;
    }
    public static function sizeOfDir($path){
        $bytesTotal = 0;
        $path = realpath($path);
        if(is_dir($path)){
            foreach(new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($path, \FilesystemIterator::SKIP_DOTS)) as $object){
                $bytesTotal += $object->getSize();
            }
        }
        return $bytesTotal;
    }

    public static function readFileToArray($filename , $flags = null){
        if(is_null($flags)){
            return file($filename, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        }
        return file($filename, $flags);
    }

    public static function delete($filename): bool
    {
        return unlink($filename);
    }

    public static function deleteDir($dir): bool
    {
        $iterator = new \RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS);
        $files = new \RecursiveIteratorIterator($iterator,
            \RecursiveIteratorIterator::CHILD_FIRST);
        foreach($files as $file) {
            if ($file->isDir()){
                rmdir($file->getRealPath());
            } else {
                unlink($file->getRealPath());
            }
        }
        return rmdir($dir);
    }



}
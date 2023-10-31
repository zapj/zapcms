<?php
/*
 * Copyright (c) 2023.  ZAP.CN  - ZAP CMS
 */


function site_url($path): string
{
    $site_url = config('config.site_url',base_url());
    return $site_url . $path;
}

function home_url(){
    return config('config.site_url',base_url()) ?: '/';
}

function node_url(){

}

function url_link($path){

}

function catalog_link($path,$title){

}

function theme_url($path = null): string
{
    $theme = config('config.theme','basic');
    return site_url("/themes/{$theme}/{$path}");
}

function theme_path($path = null){
    $theme = config('config.theme','basic');
    return base_path("/themes/{$theme}/{$path}");
}

function theme_file_is_exists($file,$extList = null): bool
{
    $extList = $extList ?? ['.php','.twig'];
    if(!is_array($extList)){
        $extList = [$extList];
    }
    foreach ($extList as $ext){
        if(is_file(theme_path($file.$ext))){
            return true;
        }
    }
    return false;
}
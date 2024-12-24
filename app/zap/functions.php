<?php

function mod_path($mod_name){
    return base_path('mods/'.$mod_name);
}

function get_option($option_name, $default = null,$ttl = null){
    return \zap\facades\Cache::get($option_name,function() use ($option_name,$default){
        return \zap\cms\Option::get($option_name,$default);
    },$ttl);
}

function get_options($option_name,$type = '=' , $ttl = 10000){
    if(is_array($option_name)){
        $option_name = join('|',$option_name);
    }
    return \zap\facades\Cache::get($option_name,function() use ($option_name,$type){
        return \zap\cms\Option::getArray($option_name,$type);
    },$ttl);
}

function option($name,$default = null){
    $group = explode('.',$name)[0];
    if(!app()->has('options_'.$group)){
        app()->set('options_'.$group , get_options($group,'REGEXP'));
    }
    return app()->get('options_'.$group)[$name] ?? $default;
}

function option_get_json($name,$default = null,?bool $associative = null){
    $value = json_decode(option($name,$default),$associative);
    if(json_last_error() === JSON_ERROR_NONE){
        return $value;
    }
    return $default;
}

function option_get_unserialize($name,$default = null,?bool $associative = null){
    $value = @unserialize(option($name,$default),$associative);
    if(!$value ){
        return $default;
    }
    return $value;
}


function pageState() : \app\PageState {
    if(!app()->has('page_state')){
        app()->page_state = new \app\PageState();
    }
    return app()->page_state;
}

function url_slug(...$args): string
{
    $uri = [];
    foreach ($args as $arg){
        if(empty($arg)){
            continue;
        }
        $uri[] = is_array($arg) ? join('/',$arg) : $arg;
    }
    return base_url('/' . join('/',$uri));

}



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
    $theme = option('website.theme','basic');
    return site_url("/themes/{$theme}/{$path}");
}

function theme_path($path = null): string
{
    $theme = option('website.theme','basic');
    return base_path("themes/{$theme}/{$path}");
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


function zap_var_export($expression, $return=FALSE) {
    $export = var_export($expression, TRUE);
    $patterns = [
        "/array \(/" => '[',
        "/^([ ]*)\)(,?)$/m" => '$1]$2',
        "/=>[ ]?\n[ ]+\[/" => '=> [',
        "/([ ]*)(\'[^\']+\') => ([\[\'])/" => '$1$2 => $3',
    ];
    $export = preg_replace(array_keys($patterns), array_values($patterns), $export);
    if (!$return){
        echo $export;
    }
    return $export;
}

function if_echo($expr,$str)
{
    if($expr){
        echo $str;
    }
}

function if_else_echo($expr,$str,$str2)
{
    if($expr){
        echo $str;
    }else{
        echo $str2;
    }
}

function if_return($expr,$str)
{
    if($expr){
        return $str;
    }
    return null;
}

function get_theme_name(){
    $theme = config('config.theme','basic');
    if($theme === false){
        return false;
    }
    return $theme;
}


function get_theme_path($path = null){
    $theme = config('config.theme','basic');
    if($theme === false){
        return false;
    }
    if($path===null){
        return base_path("themes/{$theme}");
    }
    return base_path("themes/{$theme}/{$path}");
}

function get_theme_url($path = null){
    $theme = config('config.theme','basic');
    if($theme === false){
        return false;
    }
    if($path===null){
        return base_url("themes/{$theme}");
    }
    return base_url("themes/{$theme}/{$path}");
}
<?php

function mod_path($mod_name){
    return base_path('/mods/'.$mod_name);
}

function get_option($option_name, $default = null,$ttl = null){
    return \zap\facades\Cache::get($option_name,function() use ($option_name,$default){
        return \zap\Option::get($option_name,$default);
    },$ttl);
}

function get_options($option_name,$type = '=' , $ttl = 10000){
    if(is_array($option_name)){
        $option_name = join('|',$option_name);
    }
    return \zap\facades\Cache::get($option_name,function() use ($option_name,$type){
        return \zap\Option::getArray($option_name,$type);
    },$ttl);
}

function option($name,$default = null){
    $group = explode('.',$name)[0];
    if(!app()->has('options_'.$group)){
        app()->set('options_'.$group , get_options($group,'REGEXP'));
    }
    return app()->get('options_'.$group)[$name] ?? $default;
}


function page() : \app\Page {
    if(!app()->has('page')){
        app()->make(\app\Page::class,[],'page');
    }
    return app()->page;
}

function url_slug(...$args){
    $uri = [];
    foreach ($args as $arg){
        if(empty($arg)){
            continue;
        }
        $uri[] = is_array($arg) ? join('/',$arg) : $arg;
    }
    return base_url('/' . join('/',$uri));

}

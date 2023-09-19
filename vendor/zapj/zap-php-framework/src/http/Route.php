<?php 

namespace zap\http;

class Route {


    public static function get($pattern, $action){
        return app()->router->get($pattern,$action);
    }

    public static function post($pattern, $action){
        return app()->router->post($pattern,$action);
    }

    public static function put($pattern, $action){
        return app()->router->put($pattern,$action);
    }

    public static function delete($pattern, $action){
        return app()->router->delete($pattern,$action);
    }

    public static function any($pattern, $action){
        return app()->router->any($pattern,$action);
    }

    public static function resources($prefix, $class){
        Route::get($prefix, "{$class}@index");
        Route::get("$prefix/create", "{$class}@create");
        Route::post("$prefix/save", "{$class}@save");
        Route::get("$prefix/{id:\d+}", "{$class}@show");
        Route::get("$prefix/{id:\d+}/edit", "{$class}@edit");
        Route::put("$prefix/{id:\d+}", "{$class}@update");
        Route::delete("$prefix/{id:\d+}", "{$class}@destroy");
    }

    public static function prefix($pattern, $fn, $options = []){
        return app()->router->prefix($pattern, $fn, $options);
    }

    public static function filter($methods, $pattern, $fn, $options = []){
        return app()->router->filter($methods, $pattern, $fn, $options);
    }
}
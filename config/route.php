<?php
use zap\http\Route;
Route::prefix("/zap/",function (){
    config_set('config.theme',false);
    \zap\view\View::paths(base_path('/app/zap/views'));
},["namespace"=>'\app\zap\controllers']);
Route::get("/",function (){
    echo "hello world";
});
Route::get("/hello",function (){
   echo "hello";
});

Route::get("/json",function (){
    \zap\http\Response::json(['code'=>200]);
});

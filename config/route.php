<?php
use zap\http\Route;
use zap\view\View;

Route::prefix("/z-admin/",function (){

    config_set('config.theme',false);
    View::paths(base_path('/app/zap/views'));
},[
    "namespace"=>'\app\zap\controllers'
]);
Route::get("/",function (){
    View::render('index');
});
Route::get("/hello",function (){
   echo "hello";
});

Route::any("/json",function (){

    $user = \zap\http\Request::post('user');
    \zap\http\Response::json(['code'=>200,'user'=>$user]);
});

<?php
use zap\http\Route;
use zap\view\View;

Route::prefix("/z-admin/",\zap\Bootstrap::class,[
    "namespace"=>'\app\zap\controllers'
]);

Route::get("/",function (){
    View::render('index');
});
Route::get("/hello(/{name:\d+}(/{age:\d+})?)?",function ($name){
    print_r(func_get_args());
   echo "hello",$name;
});

Route::any("/json",function (){

    $user = \zap\http\Request::post('user');
    \zap\http\Response::json(['code'=>200,'user'=>$user]);
});

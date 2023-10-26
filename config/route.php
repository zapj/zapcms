<?php
use zap\http\Route;
use zap\view\View;

Route::prefix("/z-admin/",\zap\Bootstrap::class,[
    "namespace"=>'\app\zap\controllers'
]);

Route::prefix("/",\app\Startup::class,[
    "namespace"=>'\app\controllers'
]);

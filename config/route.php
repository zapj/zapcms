<?php
use zap\http\Route;

const ADMIN_PREFIX = "/z-admin/";

Route::prefix(ADMIN_PREFIX,\zap\Bootstrap::class,[
    "namespace"=>'\app\zap\controllers'
]);

Route::prefix("/",\app\Startup::class,[
    "namespace"=>'\app\controllers',
]);

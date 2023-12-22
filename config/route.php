<?php
use zap\http\Route;

Route::prefix(Z_ADMIN_PREFIX,\zap\Bootstrap::class,["namespace"=>'\app\zap\controllers']);

Route::prefix("/",\app\Startup::class,["namespace"=>'\app\controllers']);


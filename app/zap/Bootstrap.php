<?php
namespace zap;

use zap\http\Middleware;
use zap\view\View;

class Bootstrap implements Middleware
{

    public function __construct()
    {

    }

    public function handle()
    {
        define('IN_ZAP_ADMIN',true);
        config_set('config.theme',false);
        View::paths(base_path('/app/zap/views'));
    }
}
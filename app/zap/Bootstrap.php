<?php
namespace zap;

use zap\http\Middleware;
use zap\http\Request;
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
        define('IS_AJAX',Request::isAjax());
        View::paths(realpath(__DIR__ . '/views'));
        $theme = option('website.theme','basic');
        if(is_file(themes_path("{$theme}/functions.php"))){
            include themes_path("{$theme}/functions.php");
        }
//        View::paths(base_path(__DIR__ . '/views'));
    }
}
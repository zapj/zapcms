<?php
/*
 * Copyright (c) 2023.  ZAP.CN  - ZAP CMS - All Rights Reserved
 * @author Allen
 * @email zapcms@zap.cn
 * @date 2023/12/27 上午11:38
 * @lastModified 2023/12/27 上午11:30
 *
 */

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
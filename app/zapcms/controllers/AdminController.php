<?php
/*
 * Copyright (c) 2023.  ZAP.CN  - ZAP CMS - All Rights Reserved
 * @author Allen
 * @email zapcms@zap.cn
 * @date 2023/12/27 上午11:29
 * @lastModified 2023/12/27 上午11:02
 *
 */

namespace zapcms\controllers;

use zapcms\auth\RBAC;
use zap\http\Controller;
use zap\view\View;
use zapcms\services\Auth;
use zapcms\services\BreadCrumb;

class AdminController extends Controller
{
    public function __construct()
    {
        Auth::check();
        View::share('zapAdmin',Auth::user());
        //初始化RBAC
        app()->make(RBAC::class,[],'rbac');
        app()->breadcrumb = BreadCrumb::instance();
        BreadCrumb::instance()->add('控制台',url_action('Index'));
        //website options

    }

}
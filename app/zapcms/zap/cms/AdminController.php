<?php
/*
 * Copyright (c) 2023.  ZAP.CN  - ZAP CMS - All Rights Reserved
 * @author Allen
 * @email zapcms@zap.cn
 * @date 2023/12/27 上午11:29
 * @lastModified 2023/12/27 上午11:02
 *
 */

namespace zap\cms;

use zap\cms\rbac\RBAC;
use zap\http\Controller;
use zap\view\View;

class AdminController extends Controller
{
    public function __construct()
    {
        Auth::check();
        View::share('zapAdmin',Auth::user());
        //初始化RBAC
        app()->make(RBAC::class,[],'rbac');
        app()->breadcrumb = BreadCrumb::instance();
        //website options

    }

}
<?php

namespace zap;

use zap\http\Controller;
use zap\rbac\RBAC;
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
<?php

namespace zap;

use zap\http\Controller;

use zap\view\View;

class AdminController extends Controller
{
    protected BreadCrumb $breadcrumb;
    public function __construct()
    {
        Auth::check();
        View::share('zapAdmin',Auth::user());
        $this->breadcrumb = BreadCrumb::instance();
    }

}
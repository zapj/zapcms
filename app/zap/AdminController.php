<?php

namespace zap;

use zap\http\Controller;
use zap\view\View;
use zap\Auth;

class AdminController extends Controller
{
    public function __construct()
    {
        Auth::check();
        View::share('zap_admin',session()->get('zap.admin'));
    }

}
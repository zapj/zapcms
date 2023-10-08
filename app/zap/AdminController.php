<?php

namespace zap;

use zap\http\Controller;
use zap\view\View;
use app\zap\helpers\EnvCheck;

class AdminController extends Controller
{
    public function __construct()
    {
        Auth::check();
        View::share('zapAdmin',Auth::user());
    }

}
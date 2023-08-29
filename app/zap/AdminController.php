<?php

namespace app\zap;

use zap\http\Controller;

class AdminController extends Controller
{
    public function __construct()
    {
        Auth::check();

    }

}
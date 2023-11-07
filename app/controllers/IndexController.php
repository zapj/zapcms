<?php
/*
 * Copyright (c) 2023.  ZAP.CN  - ZAP CMS
 */

namespace app\controllers;

use zap\http\Controller;

class IndexController extends Controller
{
    function index(){
        pageState()->isHome = true;
        view('index',[]);
    }
}
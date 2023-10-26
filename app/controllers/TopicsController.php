<?php
/*
 * Copyright (c) 2023.  ZAP.CN  - ZAP CMS
 */

namespace app\controllers;

use zap\http\Controller;

class TopicsController extends Controller
{
    function index(){
        view('category',[]);
    }
}
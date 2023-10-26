<?php
/*
 * Copyright (c) 2023.  ZAP.CN  - ZAP CMS
 */

namespace app\controllers;

use zap\http\Controller;

class TagsController extends Controller
{
    function index(){
        view('index',[]);
    }
}
<?php
/*
 * Copyright (c) 2023.  ZAP.CN  - ZAP CMS
 */

namespace app\controllers;

use zap\http\Controller;

class CatalogController extends Controller
{
    function index(){
        view('catalog',[]);
    }
}
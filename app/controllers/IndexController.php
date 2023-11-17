<?php
/*
 * Copyright (c) 2023.  ZAP.CN  - ZAP CMS
 */

namespace app\controllers;

use zap\http\Controller;
use zap\Node;

class IndexController extends Controller
{
    function index(){
        pageState()->isHome = true;
        $latestNews = Node::where('status',Node::STATUS_PUBLISH)
            ->where('node_type','article')
            ->limit(4)
            ->get(FETCH_ASSOC);
        view('index',['latestNews'=>$latestNews]);
    }
}
<?php
/*
 * Copyright (c) 2023.  ZAP.CN  - ZAP CMS
 */

namespace app\controllers;


use zap\http\Controller;

class SitemapController extends Controller
{

    function index(){
        echo 'sitemap index';
    }

    public function generate($type = null)
    {
        if( $type === 'sitemap.xml'){
            $this->index();
            return;
        }
        echo $type;
    }
}
<?php

namespace app\zap\controllers;

use zap\http\Controller;
use zap\view\View;

class InstallController extends Controller
{

    function index(){
        $data = [];
        View::render("install.index",$data);
    }

    function database(){
        $data = [];
        View::render("install.database",$data);
    }

}
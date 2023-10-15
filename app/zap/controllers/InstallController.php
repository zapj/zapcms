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

    function check(){
        $data = [];
        View::render("install.check",$data);
    }

    function database(){
        $data = [];
        View::render("install.database",$data);
    }

    function done(){
        $data = [];
        View::render("install.done",$data);
    }

}
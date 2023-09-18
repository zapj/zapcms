<?php

namespace app\zap\controllers;


use zap\AdminController;
use zap\facades\Url;
use zap\http\Request;
use zap\http\Response;
use zap\util\Password;
use zap\view\View;

class FinderController extends AdminController
{

    function image(){

    }

    function file(){

    }

    function faIcons(){
        View::render('finder.faicons');
    }



}
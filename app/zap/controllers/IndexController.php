<?php

namespace app\zap\controllers;

use app\zap\AdminController;
use zap\facades\Url;
use zap\util\Password;
use zap\view\View;

class IndexController extends AdminController
{
    function index(){
        $data = [];
        $data['zap_admin'] = session()->get('zap.admin');
        View::render("index.index",$data);
    }

}
<?php

namespace app\zap\controllers;

use zap\AdminController;
use zap\facades\Url;
use zap\util\Password;
use zap\view\View;

class SystemController extends AdminController
{
    function settings(){
        $data = [];
        View::render("system.settings",$data);
    }

}
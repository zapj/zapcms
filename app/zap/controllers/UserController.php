<?php

namespace app\zap\controllers;

use app\zap\AdminController;
use zap\facades\Url;
use zap\util\Password;
use zap\view\View;

class UserController extends AdminController
{
    function changePassword(){
        $data = [];
        View::render("user.change_password",$data);
    }

}
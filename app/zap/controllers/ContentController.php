<?php

namespace app\zap\controllers;

use app\zap\AdminController;
use app\zap\AdminMenu;
use zap\facades\Url;
use zap\util\Password;
use zap\view\View;

class ContentController extends AdminController
{
    function index(){
        $data = [];
        $menu = new AdminMenu();
//        $menu->add(['name'=>'编程语言'],0);
        $data['menu'] = $menu;
        View::render("admin_menu.index",$data);
    }

}
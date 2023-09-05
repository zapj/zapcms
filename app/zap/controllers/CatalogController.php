<?php

namespace app\zap\controllers;

use app\zap\AdminController;
use app\zap\AdminMenu;
use zap\facades\Url;
use zap\util\Password;
use zap\view\View;

/*
 * 栏目
 */
class CatalogController extends AdminController
{
    function index(){
        $data = [];
        $menu = new AdminMenu();
//        $menu->add(['name'=>'编程语言'],0);
        $data['menu'] = $menu;
        View::render("catalog.index",$data);
    }

    public function save()
    {
        print_r($_POST);
    }

}
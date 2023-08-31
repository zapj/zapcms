<?php

namespace app\zap\controllers;

use app\zap\AdminController;
use zap\Category;
use zap\facades\Cache;
use zap\facades\Url;
use zap\Option;
use zap\util\Password;
use zap\view\View;

class CategoryController extends AdminController
{
    function index(){
//        Option::update('web.site_slogan','ZAP WEBSITE');
//        echo Option::get('web.sitename','Hello');
//            $webconfig = Cache::get('web.configs',function(){
//                $array =  Option::getArray('web.%',[]);
//                return $array;
//            },10);
//            print_r($webconfig);

        $category = new Category('category');
        $ID = $category->add(['name'=>'编程语言'],0);
        $category->add(['name'=>'JAVa'],$ID);

    }

}
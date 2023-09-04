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
        $ID = $category->add(['name'=>'编程语言'],4);
        $category->add(['name'=>'Spring Boot'],$ID);
        $category->add(['name'=>'Spring Web'],$ID);

    }

    function show(){
        $category = new Category('category');
//        $all = $category->getAll();
        $all = $category->getTreeArray();
//        print_r($all);

        while($data = array_shift($all)){
            echo str_repeat('=',$data['level']),$data['name'],'<br/>';

            if(!empty($data['children'])){
//                array_unshift($all,$data['children'] );
                foreach ($data['children'] as $child){
                    array_unshift($all,$child);
                }

            }
        }
    }

}
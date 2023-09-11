<?php

namespace app\zap\controllers;

use zap\AdminController;

use zap\Content;
use zap\view\View;

class ModuleController extends AdminController
{

    public function _invoke($module,$params)
    {
        if($module == 'index'){
            $this->$module();
        }else{
            $controller = array_shift($params) ?? 'Index';
            $action = array_shift($params) ?? 'index';
            $controller = str_replace('-','',ucwords($controller,"-"));
            $action = str_replace('-','',ucwords($action,"-"));

            $class = "\\app\\modules\\{$module}\\controllers\\{$controller}Controller";

            if(!class_exists($class)){
//                trigger_error("{$class} - Class does not exist!!",E_USER_ERROR);
                return View::render('content.notfound',[
                    'module'=>$module,
                    'controller'=>$controller,
                    'method'=>$action,
                    'error'=> "{$class} - Class does not exist!!"
                ]);
            }
            if(!method_exists($class,$action)){
//                trigger_error("{$class}::{$action} - Method does not exist!!",E_USER_ERROR);
                return View::render('content.notfound',[
                    'module'=>$module,
                    'controller'=>$controller,
                    'method'=>$action,
                    'error'=> "{$class}::{$action} - Method does not exist!!"
                ]);
            }

            View::paths(base_path("/app/modules/{$module}/views"));
            call_user_func_array(array($class, $action), $params);

        }
    }

    function index(){
        $data = [];


        View::render("module.index",$data);
    }

}
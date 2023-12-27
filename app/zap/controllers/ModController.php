<?php

namespace app\zap\controllers;

use zap\cms\AdminController;
use zap\view\View;

class ModController extends AdminController
{

    public function _invoke($module,$params)
    {
        if($module == 'index'){
            $this->$module();
        }else{
            View::paths(base_path("app/mods/{$module}/views"));
            $controller = array_shift($params) ?? 'Index';
            $action = array_shift($params) ?? 'index';
            $controller = str_replace('-','',ucwords($controller,"-"));
            $action = str_replace('-','',ucwords($action,"-"));

            $mod = "\\app\\mods\\{$module}\\Mod";
            if(class_exists($mod)){
                return call_user_func_array(array($mod, 'invoke'), [$module,$controller,$action,$params]);
            }

            $class = "\\app\\mods\\{$module}\\controllers\\{$controller}Controller";

            if(!class_exists($class)){
//                trigger_error("{$class} - Class does not exist!!",E_USER_ERROR);
                return View::render('mod.notfound',[
                    'module'=>$module,
                    'controller'=>$controller,
                    'method'=>$action,
                    'error'=> "{$class} - Class does not exist!!"
                ]);
            }
            if(!method_exists($class,$action)){
//                trigger_error("{$class}::{$action} - Method does not exist!!",E_USER_ERROR);
                return View::render('mod.notfound',[
                    'module'=>$module,
                    'controller'=>$controller,
                    'method'=>$action,
                    'error'=> "{$class}::{$action} - Method does not exist!!"
                ]);
            }
            call_user_func_array(array(new $class(), $action), $params);
        }
    }

    function index(){
        $data = [];


        View::render("mod.index",$data);
    }

}
<?php

namespace app\zap\controllers;

use http\Env\Request;
use zap\AdminController;

use zap\http\Response;
use zap\Node;
use zap\view\View;

class ZapController extends AdminController
{

    public function _invoke($module,$params)
    {
        View::paths(base_path("/app/zap/mods/{$module}/views"));
        if($module == 'index'){
            $this->$module();
        }else{
            $isAjax = \zap\http\Request::isAjax();
            $controller = array_shift($params) ?? 'Index';
            $action = array_shift($params) ?? 'index';
            $controller = str_replace('-','',ucwords($controller,"-"));
            $action = str_replace('-','',ucwords($action,"-"));

            $class = "\\zap\\mods\\{$module}\\controllers\\{$controller}Controller";

            $respondData = ['module'=>$module,'controller'=>$controller,'method'=>$action];

            if(!class_exists($class)){
//                trigger_error("{$class} - Class does not exist!!",E_USER_ERROR);
                $respondData['error'] = "{$class} - Class does not exist!!";
                $isAjax ? Response::json($respondData) : View::render('zap.notfound',$respondData);
                return false;
            }
            if(!method_exists($class,$action)){
//                trigger_error("{$class}::{$action} - Method does not exist!!",E_USER_ERROR);
                $respondData['error'] = "{$class}::{$action} - Method does not exist!!";
                $isAjax ? Response::json($respondData) : View::render('zap.notfound',$respondData);
                return false;
            }
            call_user_func_array(array($class, $action), $params);

        }
    }

    function index(){
        $data = [];


        View::render("module.index",$data);
    }

}
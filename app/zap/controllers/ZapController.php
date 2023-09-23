<?php

namespace app\zap\controllers;

use zap\AdminController;
use zap\http\Response;
use zap\Node;
use zap\view\View;

class ZapController extends AdminController
{

    public function _invoke($controller,$params)
    {
        View::paths(base_path("/app/zap/node/views"));
        if($controller == 'index'){
            $this->$controller();
        }else{
            $isAjax = \zap\http\Request::isAjax();
//            $controller = array_shift($params) ?? 'Index';
            $action = array_shift($params) ?? 'index';
            $controller = str_replace('-','',ucwords($controller,"-"));
            $action = str_replace('-','',ucwords($action,"-"));

            $class = "\\zap\\node\\controllers\\{$controller}Controller";

            if(!class_exists($class)){
//                trigger_error("{$class} - Class does not exist!!",E_USER_ERROR);
                $respondData = ['controller'=>$controller,'method'=>$action];
                $respondData['error'] = "{$class} - Class does not exist!!";
                $respondData['code'] = -1;
                $isAjax ? Response::json($respondData) : View::render('zap.notfound',$respondData);
                return false;
            }
            if(!method_exists($class,$action)){
//                trigger_error("{$class}::{$action} - Method does not exist!!",E_USER_ERROR);
                $respondData = ['controller'=>$controller,'method'=>$action];
                $respondData['code'] = -1;
                $respondData['error'] = "{$class}::{$action} - Method does not exist!!";
                $isAjax ? Response::json($respondData) : View::render('zap.notfound',$respondData);
                return false;
            }

            $zapController = new $class();
            $zapController->controller = $controller;
            $zapController->action = $action;
            $zapController->__init();
            $zapController->$action(...$params);
//            call_user_func_array(array($zapController, $action), $params);
        }
    }

    function index(){
        $data = [];


        View::render("module.index",$data);
    }

}
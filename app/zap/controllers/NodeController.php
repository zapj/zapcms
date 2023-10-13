<?php

namespace app\zap\controllers;

use zap\AdminController;
use zap\http\Response;
use zap\Node;
use zap\node\AbstractNodeType;
use zap\NodeType;
use zap\view\View;

class NodeController extends AdminController
{

    public function _invoke($method,$params)
    {
        View::paths(base_path("/app/zap/node/views"));
        if($method == 'index'){$method = 'default';}
        if(in_array($method , ['types'])){
            $this->$method();
        }else{

            $isAjax = \zap\http\Request::isAjax();
            $action = array_shift($params) ?? 'index';
            $controller = str_replace('-','',ucwords($method,"-"));
            $action = str_replace('-','',ucwords($action,"-"));

            $class = "\\zap\\node\\controllers\\{$controller}Controller";
            if(!class_exists($class)){
                $class = AbstractNodeType::class;
            }
            if(!method_exists($class,$action)){
//                trigger_error("{$class}::{$action} - Method does not exist!!",E_USER_ERROR);
                $respondData = ['controller'=>$controller,'method'=>$action];
                $respondData['code'] = -1;
                $respondData['error'] = "{$class}::{$action} - Method does not exist!!";
                $isAjax ? Response::json($respondData) : View::render('node.notfound',$respondData);
                return false;
            }

            $zapController = new $class();
            $zapController->controller = $controller;
            $zapController->action = $action;
            $nodeTypeId =  NodeType::getID($controller);
            is_null($nodeTypeId) or $zapController->setNodeType($nodeTypeId);
            $zapController->setTitle(NodeType::getTitle($controller));
            $zapController->setNodeType(NodeType::getID($controller) ?? 0);
            $zapController->setCatalogId(req()->get('catalog_id',0));
            $zapController->__init();
            $zapController->$action(...$params);

//            call_user_func_array(array($zapController, $action), $params);
        }
    }

    function index(){
        $data = [];


        View::render("node.index",$data);
    }

    function types(){
        $data = [];


        View::render("node.types",$data);
    }

}
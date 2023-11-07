<?php

namespace app\zap\controllers;

use zap\AdminController;
use zap\http\Response;
use zap\http\Router;
use zap\Node;
use zap\node\AbstractNodeType;
use zap\NodeType;
use zap\view\View;

class NodeController extends AdminController
{

    public function _invoke($method,$params)
    {
        View::paths(base_path("app/zap/node/views"));
        if($method == 'index'){$method = 'default';}
        if(in_array($method , ['types','typesForm'])){
            $this->$method();
        }else{
            $action = array_shift($params) ?? 'index';
            $controller = Router::convertToName($method);
            $action = Router::convertToName($action);


            $class = "\\zap\\node\\controllers\\{$controller}Controller";
            if(!class_exists($class)){
                $nodeTypeClass = NodeType::getClass($method);
                $class = class_exists($nodeTypeClass) ? $nodeTypeClass : AbstractNodeType::class;
            }
            if(!method_exists($class,$action)){
//                trigger_error("{$class}::{$action} - Method does not exist!!",E_USER_ERROR);
                $respondData = ['controller'=>$controller,'method'=>$action];
                $respondData['code'] = -1;
                $respondData['error'] = "{$class}::{$action} - Method does not exist!!";
                IS_AJAX ? Response::json($respondData) : View::render('node.notfound',$respondData);
                return false;
            }
            $typeName = Router::convertToUrlName($method);
            $zapController = new $class();
            $zapController->controller = $controller;
            $zapController->action = $action;
//            $nodeTypeId =  NodeType::getID($controller);
//            is_null($nodeTypeId) or $zapController->setNodeType($nodeTypeId);
            $zapController->setTitle(NodeType::getTitle($typeName));
            $zapController->setNodeType($typeName);
            $zapController->setCatalogId(req()->get('cid',0));
            $zapController->__init();
            $zapController->$action(...$params);

//            call_user_func_array(array($zapController, $action), $params);
        }
    }

    function types(){
        $data = [];


        View::render("node.types",$data);
    }

}
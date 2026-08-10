<?php

namespace zapcms\controllers;

use zapcms\controllers\AdminController;
use zapcms\services\NodeType;
use zap\DB;
use zap\http\Response;
use zap\http\Router;
use zapcms\node\AbstractNodeType;
use zap\view\View;

class NodeController extends AdminController
{

    public function _invoke(string $method, array $params = [])
    {
        View::paths(base_path("app/zapcms/node/views"));
        if($method == 'index'){$method = 'default';}
        if(in_array($method , ['types','typesForm'])){
            $this->$method();
        }else{
            $action = array_shift($params) ?? 'index';
            $controller = Router::convertToName($method);
            $action = Router::convertToName($action);

            $class = "\\zapcms\node\controllers\{$controller}Controller";
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
            $typeName = strtolower($method);
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
        $search  = req()->get('search', '');
        $status  = req()->get('status', '');

        $query = DB::table('node_types');
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('type_name', 'LIKE', "%{$search}%")
                  ->orWhere('title', 'LIKE', "%{$search}%");
            });
        }
        if ($status !== '') {
            $query->where('status', (int)$status);
        }
        $query->orderBy('sort_order', 'ASC')->orderBy('type_id', 'DESC');

        $data = $query->fetchAll();

        View::render("node.types", [
            'data'        => $data,
            'search'      => $search,
            'status'      => $status,
            'title'       => '内容模型管理',
            'page_title'  => '内容模型',
            'page_subtitle' => '管理网站的内容类型',
        ]);
    }

    function typesForm(){
        $id  = req()->get('id', 0);
        $row = [];
        if ($id > 0) {
            $row = DB::table('node_types')->where('type_id', (int)$id)->first();
            if (!$row) {
                throw new \RuntimeException('模型不存在');
            }
        }
        View::render("node.types_form", [
            'row'         => $row,
            'id'          => (int)$id,
            'page_title'  => $id > 0 ? '编辑模型' : '添加模型',
        ]);
    }

    function typesSave(){
        $id   = req()->post('type_id', 0);
        $data = [
            'type_name'   => trim(req()->post('type_name', '')),
            'title'       => trim(req()->post('title', '')),
            'description' => trim(req()->post('description', '')),
            'node_type'   => trim(req()->post('node_type', '')),
            'version'     => trim(req()->post('version', '0.0.0')),
            'sort_order'  => (int)req()->post('sort_order', 0),
            'status'      => (int)req()->post('status', 1),
        ];

        if (empty($data['type_name']) || empty($data['title'])) {
            Response::json(['code' => 1, 'msg' => '类型标识和标题不能为空']);
            return;
        }

        // 唯一性检查
        $existQuery = DB::table('node_types')->where('type_name', $data['type_name']);
        if ($id > 0) {
            $existQuery->where('type_id', '!=', (int)$id);
        }
        if ($existQuery->exists()) {
            Response::json(['code' => 1, 'msg' => '类型标识已存在']);
            return;
        }

        $now = time();
        if ($id > 0) {
            $data['updated_at'] = $now;
            DB::table('node_types')->where('type_id', (int)$id)->update($data);
        } else {
            $data['created_at'] = $now;
            $data['updated_at'] = $now;
            DB::table('node_types')->insert($data);
        }

        Response::json(['code' => 0, 'msg' => '保存成功']);
    }

    function typesDelete(){
        $id = req()->post('id', 0);
        if ($id <= 0) {
            Response::json(['code' => 1, 'msg' => '参数错误']);
            return;
        }
        DB::table('node_types')->where('type_id', (int)$id)->delete();
        Response::json(['code' => 0, 'msg' => '删除成功']);
    }

}
<?php

namespace zap\mods\news\controllers;


use zap\http\Request;
use zap\http\Response;
use zap\Node;
use zap\view\View;

class IndexController
{
    public function index(){
        $data['title'] = '新闻管理';
        $data['data'] = Node::findAll(['node_type'=>1],['orderBy'=>'id desc','limit'=>[20,0]]);

        View::render('index.index',$data);
    }

    public function edit($id){
        $data['title'] = '编辑新闻';
        $data['node'] = Node::findById($id);
        View::render('index.form',$data);
    }

    public function add()
    {
        if(Request::isPost()){
            $node = Request::post('node');
            $node['node_type'] = 1;
            if(empty($node['pub_time'])){
                $node['pub_time'] = time();

            }else{
                $node['pub_time'] = strtotime($node['pub_time']);
            }
            Node::create($node);
            Response::json(['code'=>0,'msg'=>'创建成功']);
            return;
        }
        $data['title'] = '添加新闻';
        $data['node'] = new Node();
        View::render('index.form',$data);
    }

    function remove(){
        $id = intval(Request::post('id'));
        if(Request::isPost() && $id){
            $affId = Node::delete($id);
            if($affId){
                Response::json(['code'=>0,'msg'=>'删除成功']);
            }else{
                Response::json(['code'=>1,'msg'=>'删除失败，ID不存在']);
            }
        }
        Response::json(['code'=>1,'msg'=>'删除失败，ID不存在']);
    }
}
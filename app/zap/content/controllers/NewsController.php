<?php

namespace zap\content\controllers;


use zap\facades\Url;
use zap\helpers\Pagination;
use zap\http\Request;
use zap\http\Response;
use zap\http\Session;
use zap\Node;
use zap\view\View;

class NewsController
{
    public function index(){

        $data['title'] = '新闻管理';
        $page = new Pagination(intval(Request::all('page')),2,Request::get());
        $page->setTotal(Node::count('id',['node_type'=>1]));
        $data['data'] = Node::findAll(['node_type'=>1],['orderBy'=>'id desc','limit'=>[$page->getLimit(),$page->getOffset()]]);
        $data['page'] = $page;
        View::render('news.index',$data);
    }

    public function edit($id = 0){
        if(!$id){
            Response::redirect(url_action("Zap@News"),"文章不存在",Session::ERROR);

        }
        if(Request::isPost()){
            $node = Request::post('node');
            $node['update_time'] = time();
            if(empty($node['pub_time'])){
                $node['pub_time'] = time();
            }else{
                $node['pub_time'] = strtotime($node['pub_time']);
            }
            Node::updateAll($node,['id'=>$id]);
            Response::json(['code'=>0,'msg'=>'修改成功','id'=>$id]);
            return;
        }
        $data['title'] = '编辑新闻';
        $data['node'] = Node::findById($id);
        View::render('news.form',$data);
    }

    public function add()
    {
        if(Request::isPost()){
            $node = Request::post('node');
            $node['node_type'] = 1;
            $node['add_time'] = time();
            $node['update_time'] = time();
            if(empty($node['pub_time'])){
                $node['pub_time'] = time();
            }else{
                $node['pub_time'] = strtotime($node['pub_time']);
            }
            $id = Node::create($node);
            Response::json(['code'=>0,'msg'=>'创建成功','id'=>$id]);
            return;
        }
        $data['title'] = '添加新闻';
        $data['node'] = new Node();
        View::render('news.form',$data);
    }

    function remove(){
        $id = intval(Request::post('id'));
        if(Request::isPost() && $id){
            $affId = Node::delete($id);
            if($affId){
                add_flash('删除成功',FLASH_SUCCESS);
                Response::json(['code'=>0,'msg'=>'删除成功']);
            }else{
                Response::json(['code'=>1,'msg'=>'删除失败，ID不存在']);
            }
        }
        Response::json(['code'=>1,'msg'=>'删除失败，ID不存在']);
    }
}
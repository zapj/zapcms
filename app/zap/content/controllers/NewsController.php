<?php

namespace zap\content\controllers;


use zap\Catalog;
use zap\facades\Url;
use zap\helpers\Pagination;
use zap\http\Request;
use zap\http\Response;
use zap\http\Session;
use zap\Node;
use zap\NodeRelation;
use zap\NodeType;
use zap\view\View;

class NewsController
{
    public function index(){

        $data['title'] = '新闻管理';
        $page = new Pagination(intval(Request::all('page')),10,Request::get());
        $page->setTotal(Node::count('id',['node_type'=>1]));
        $data['data'] = Node::findAll(['node_type'=>1],['orderBy'=>'id desc','limit'=>[$page->getLimit(),$page->getOffset()]]);
        $data['page'] = $page;
        View::render('news.index',$data);
    }

    public function edit($id = 0){
        $id = intval($id);
        if(!$id){
            Response::redirect(url_action("Zap@News"),"文章不存在",Session::ERROR);
        }
        $catalog_id = intval(Request::get('catalog_id'));
        if(Request::isPost()){
            $node = Request::post('node');
            $catalogArray = Request::post('catalog',[]);
            $node['update_time'] = time();
            $node['pub_time'] = strtotime($node['pub_time']) ?: time();
            NodeRelation::delete(['node_id'=>$id]);
            foreach ($catalogArray as $catalog_id){
                NodeRelation::create(['node_id'=>$id,'catalog_id'=>$catalog_id,'node_type'=>NodeType::NEWS]);
            }
            Node::updateAll($node,['id'=>$id]);
            Response::json(['code'=>0,'msg'=>'修改成功','id'=>$id]);
            return;
        }
        $data['title'] = '编辑新闻';
        $data['node'] = Node::findById($id);
        $catalog = Catalog::instance()->get($catalog_id);
        $rootId = explode(',',$catalog['path'])[0] ?? 0;
        $conditions = [];
        if($rootId){
//            $conditions[] = ['path','REGEXP',"^{$rootId},"];
            $conditions['node_type'] = $catalog['node_type'];
        }

        $data['node_relations'] = NodeRelation::find(['node_id'=>$id])->select('catalog_id,node_id')->get(FETCH_KEY_PAIR);
        $data['catalogList'] = Catalog::instance()->getTreeArray($conditions);
        View::render('news.form',$data);
    }

    public function add()
    {
        $catalog_id = intval(Request::get('catalog_id'));
        if(Request::isPost()){
            $node = Request::post('node');
            $node['node_type'] = NodeType::NEWS;
            $node['add_time'] = time();
            $node['update_time'] = time();
            $node['pub_time']  = strtotime($node['pub_time']) ?: time();
            $node = Node::create($node);

            Response::json(['code'=>0,'msg'=>'创建成功','id'=>$node->id,'redirect_to'=>url_action("Zap@News/edit/{$node->id}",$_GET)]);

        }
        $data['title'] = '添加新闻';
        $data['node'] = new Node();
        $catalog = Catalog::instance()->get($catalog_id);
        $rootId = explode(',',$catalog['path'])[0] ?? 0;
        $conditions = [];
        if($rootId){
//            $conditions[] = ['path','REGEXP',"^{$rootId},"];
            $conditions['node_type'] = $catalog['node_type'];
        }

        $data['node_relations'] = [];
        $data['catalogList'] = Catalog::instance()->getTreeArray($conditions);
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
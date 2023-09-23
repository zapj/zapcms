<?php

namespace zap\node\controllers;


use zap\node\AbstractType;
use zap\Catalog;
use zap\helpers\Pagination;
use zap\http\Request;
use zap\http\Response;
use zap\http\Session;
use zap\Node;
use zap\NodeRelation;
use zap\NodeType;
use zap\view\View;

class NewsController extends AbstractType
{

    protected $nodeType = NodeType::NEWS;

    public function __init()
    {
        $this->title = '新闻';
    }

    public function index(){

        $data['catalogPaths'] = $this->getCurrentCatalogPath();
        $data['title'] = $this->getTitle("%s管理");
        $page = new Pagination(intval(Request::get('page')),20,Request::get());
        $page->setTotal($this->getAllTotalRows(['catalog_id'=>$this->catalogId,'node_type'=>$this->nodeType]));

        $data['data'] = $this->getAll([
            'catalog_id'=>$this->catalogId,
            'node_type'=>$this->nodeType,
            'limit'=>[$page->getLimit(),$page->getOffset()]
        ]);
        $data['page'] = $page;
        $this->display($data);
    }

    public function edit($id = 0){
        $id = intval($id);
        if(!$id){
            Response::redirect(url_action("Zap@News",$_GET),$this->getTitle("%s不存在"),Session::ERROR);
        }
        if(Request::isPost()){
            $node = Request::post('node');
            $catalogArray = Request::post('catalog',[]);
            $node['update_time'] = time();
            $node['pub_time'] = strtotime($node['pub_time']) ?: time();
            NodeRelation::delete(['node_id'=>$id]);
            foreach ($catalogArray as $catalog_id){
                NodeRelation::create(['node_id'=>$id,'catalog_id'=>$catalog_id,'node_type'=>$this->nodeType]);
            }
            Node::updateAll($node,['id'=>$id]);
            Response::json(['code'=>0,'msg'=>$this->getTitle("%s修改成功"),'id'=>$id]);
            return;
        }
        $data['title'] = $this->getTitle("修改%s");
        $data['node'] = Node::findById($id);
        $catalog = Catalog::instance()->get($this->catalogId);
        $data['node_relations'] = $this->getNodeRelationships($id);
        $data['catalogList'] = Catalog::instance()->getTreeArray(['node_type'=>$catalog['node_type']]);
        $this->display($data,'form');
    }

    public function add()
    {
        if(Request::isPost()){
            $node = Request::post('node');
            $catalogArray = Request::post('catalog',[]);
            $node['node_type'] = NodeType::NEWS;
            $node['add_time'] = time();
            $node['update_time'] = time();
            $node['pub_time']  = strtotime($node['pub_time']) ?: time();
            $node = Node::create($node);
            foreach ($catalogArray as $catalog_id){
                NodeRelation::create(['node_id'=>$node->id,'catalog_id'=>$catalog_id,'node_type'=>NodeType::NEWS]);
            }
            Response::json(['code'=>0,'msg'=> $this->title . '创建成功','id'=>$node->id,'redirect_to'=>url_action("Zap@News/edit/{$node->id}",$_GET)]);

        }
        $data['title'] = $this->getTitle("添加%s");
        $data['node'] = new Node();
        $catalog = $this->getCatalogById($this->catalogId);
        $data['node_relations'] = [];
        $data['catalogList'] = Catalog::instance()->getTreeArray(['node_type'=>$catalog['node_type']]);
        $this->display($data,'form');
    }

    function remove(){
        $id = intval(Request::post('id'));
        if(Request::isPost() && $id){
            $affId = Node::delete($id);
            if($affId){
                add_flash($this->title . '删除成功',FLASH_SUCCESS);
                Response::json(['code'=>0,'msg'=>$this->title . '删除成功']);
            }else{
                Response::json(['code'=>1,'msg'=>$this->title . '删除失败，ID不存在']);
            }
        }
        Response::json(['code'=>1,'msg'=>$this->title . '删除失败，ID不存在']);
    }
}
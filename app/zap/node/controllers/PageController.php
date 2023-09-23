<?php

namespace zap\node\controllers;

use zap\Catalog;
use zap\http\Request;
use zap\http\Response;
use zap\http\Session;
use zap\Node;
use zap\node\AbstractType;
use zap\NodeRelation;

class PageController extends AbstractType
{
    public function __init()
    {
        $this->title = '单页';
    }

    public function index()
    {
        if(Request::isPost()){
            $node = Request::post('node');
            $catalogArray = Request::post('catalog',[]);
            $node['update_time'] = time();
            $node['pub_time'] = strtotime($node['pub_time']) ?: time();
//            NodeRelation::delete(['node_id'=>$id]);
//            foreach ($catalogArray as $catalog_id){
//                NodeRelation::create(['node_id'=>$id,'catalog_id'=>$catalog_id,'node_type'=>$this->nodeType]);
//            }
//            Node::updateAll($node,['id'=>$id]);
            Response::json(['code'=>0,'msg'=>$this->getTitle("%s修改成功"),'id'=>$id]);
            return;
        }
        $data['title'] = $this->getTitle("修改%s");
        $data['node'] = new Node();
        $catalog = Catalog::instance()->get($this->catalogId);
//        $data['node_relations'] = $this->getNodeRelationships($id);
        $data['catalogList'] = Catalog::instance()->getTreeArray(['node_type'=>$catalog['node_type']]);
        $this->display($data,'form');
    }
}
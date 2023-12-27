<?php

namespace zap\node\controllers;

use zap\cms\models\Node;
use zap\db\Query;
use zap\helpers\Pagination;
use zap\node\AbstractNodeType;
use zap\view\View;

class PageController extends AbstractNodeType
{
    public function index()
    {
        if($this->catalogId==0){
//            $this->redirectTo('Node');
            $this->title = '单页';
            $page = new Pagination(req()->get('page'),20,req()->get());
            $page->setTotal($this->getPageTotal());
            $this->display([
                'page'=> $page,
                'title'=> $this->getTitle('%s管理'),
                'data'=>$this->getPages()
            ]);
            return;
        }
        View::share('catalog',$this->getCatalogById($this->catalogId));
        parent::edit($this->catalogId);

//        $node = $this->getNodeByCatalogId($this->catalogId);
//        $this->title = $node['title'];
//        if($node){
//            parent::edit($node['id']);
//        }else{
//            parent::add();
//        }
//
    }

    public function getPageTotal(){
        $query = Node::where('node_type','page');
        $query->orWhere(function(Query $query){
            $query->where('node_type','catalog')->where('mime_type','page');
        });
        return $query->count('id');
    }

    public function getPages(){
        $query = Node::where('node_type','page');
        $query->orWhere(function(Query $query){
            $query->where('node_type','catalog')->where('mime_type','page');
        });
        return $query->get(FETCH_ASSOC);
    }


}
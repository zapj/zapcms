<?php

namespace zap\node\controllers;

use zap\Catalog;
use zap\component\Hooks;
use zap\http\Request;
use zap\http\Response;
use zap\http\Session;
use zap\Node;
use zap\node\AbstractNodeType;
use zap\NodeRelation;

class PageController extends AbstractNodeType
{
    public function __init()
    {
        $this->title = '单页';
//        if($this->action == 'Index'){
//            add_filter('');
//        }
    }

    public function index()
    {
        $node = $this->getNodeByCatalogId($this->catalogId);
        $this->title = $node['title'];
        if($node){
            parent::edit($node['id']);
        }else{
            parent::add();
        }

    }


}
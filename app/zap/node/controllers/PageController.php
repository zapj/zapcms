<?php

namespace zap\node\controllers;

use zap\Catalog;
use zap\component\Hooks;
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
//        if($this->action == 'Index'){
//            add_filter('');
//        }
    }

    public function index()
    {

//        add_action('test',[$this,'add1']);
//        $a = 1;
//        do_action('test',[&$a]);

//        echo $a;
//        NodeRelation::findByCategoryId($this->catalogId);
        parent::index();
    }

    function add1($val){
        $val[0] = 32;

//        print_r();
    }
}
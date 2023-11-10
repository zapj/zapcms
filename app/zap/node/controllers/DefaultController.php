<?php

namespace zap\node\controllers;


use zap\BreadCrumb;
use zap\node\AbstractNodeType;
use zap\NodeType;

class DefaultController extends AbstractNodeType
{

    protected $nodeType;

    protected $catalogId;

    public function __init()
    {
        $this->title = '内容';
        add_filter('node_total_conditions',function($conditions){
            unset($conditions['where']['n.node_type']);
            $conditions['where'][] = ['n.node_type','NOT IN',['catalog']];
            unset($conditions['where']['nr.catalog_id']);
            return $conditions;
        });
        add_filter('node_get_all_conditions',function($conditions){
            unset($conditions['where']['n.node_type']);
//            $conditions['where'][] = ['n.node_type','!=','catalog'];
//            $conditions['where'][] = ['n.node_type','IN',['catalog']];
            unset($conditions['where']['nr.catalog_id']);
            return $conditions;
        });

    }



}
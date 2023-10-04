<?php

namespace zap\node\controllers;


use zap\node\AbstractNodeType;
use zap\NodeType;

class News1Controller extends AbstractNodeType
{

    protected $nodeType = NodeType::NEWS;

    public function __init()
    {
        $this->title = '新闻';
        add_filter('node_get_all_conditions',function($conditions) {

            return $conditions;
        });
    }






}
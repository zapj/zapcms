<?php

namespace zap\node\controllers;


use zap\node\AbstractNodeType;
use zap\NodeType;

class ProductController extends AbstractNodeType
{

    protected $nodeType = NodeType::PRODUCT;

    public function __init()
    {
        $this->title = '产品';
    }



}
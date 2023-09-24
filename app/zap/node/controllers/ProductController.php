<?php

namespace zap\node\controllers;


use zap\node\AbstractType;
use zap\NodeType;

class ProductController extends AbstractType
{

    protected $nodeType = NodeType::PRODUCT;

    public function __init()
    {
        $this->title = '产品';
    }



}
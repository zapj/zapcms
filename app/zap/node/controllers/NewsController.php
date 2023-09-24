<?php

namespace zap\node\controllers;


use zap\node\AbstractType;
use zap\NodeType;

class NewsController extends AbstractType
{

    protected $nodeType = NodeType::NEWS;

    public function __init()
    {
        $this->title = '新闻';
    }






}
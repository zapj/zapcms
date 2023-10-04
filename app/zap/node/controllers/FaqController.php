<?php

namespace zap\node\controllers;


use zap\node\AbstractNodeType;
use zap\NodeType;

class FaqController extends AbstractNodeType
{

    protected $nodeType = NodeType::FAQ;

    public function __init()
    {
        $this->title = 'FAQ';
    }



}
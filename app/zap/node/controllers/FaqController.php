<?php

namespace zap\node\controllers;


use zap\node\AbstractType;
use zap\NodeType;

class FaqController extends AbstractType
{

    protected $nodeType = NodeType::FAQ;

    public function __init()
    {
        $this->title = 'FAQ';
    }



}
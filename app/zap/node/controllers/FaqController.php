<?php

namespace zap\node\controllers;


use zap\node\AbstractNodeType;
use zap\NodeType;

class FaqController extends AbstractNodeType
{
    public function __init()
    {
        $this->title = 'FAQ';
    }



}
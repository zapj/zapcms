<?php

namespace zap\node\controllers;


use zap\node\AbstractNodeType;

class ArticleController extends AbstractNodeType
{

    public function __init()
    {
        $this->title = '文章';
    }


}

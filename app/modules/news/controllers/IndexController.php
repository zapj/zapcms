<?php

namespace app\modules\news\controllers;

use zap\Asset;
use zap\Content;
use zap\view\View;

class IndexController
{
    public function index(){
        $data['title'] = '新闻管理';
        $data['data'] = Content::findAll([],['orderBy'=>'id desc','limit'=>[1,20]]);
        View::render('index.index',$data);
    }

    public function add()
    {
        $data['title'] = '添加新闻';

        View::render('index.add',$data);
    }
}
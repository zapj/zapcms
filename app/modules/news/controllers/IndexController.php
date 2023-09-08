<?php

namespace app\modules\news\controllers;

use zap\Content;
use zap\view\View;

class IndexController extends Content
{
    public function index(){
        View::render('index.index');
    }
}
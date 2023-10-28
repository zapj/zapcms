<?php

namespace app\zap\controllers;

use zap\AdminController;
use zap\facades\Url;
use zap\Node;
use zap\util\Password;
use zap\view\View;

class IndexController extends AdminController
{
    function index(){
        $data = [];
        $node = new Node();
        $data['node_types_statistics'] = $node->getAllTypesCount();
        $data['pages'] = $node->getPages();
        View::render("dashboard.index",$data);
    }

}
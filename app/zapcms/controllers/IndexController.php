<?php

namespace zapcms\controllers;

use zapcms\controllers\AdminController;
use zapcms\models\Node;
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
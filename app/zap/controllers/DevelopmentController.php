<?php

namespace app\zap\controllers;

use zap\AdminController;
use zap\view\View;

class DevelopmentController extends AdminController
{
    public function index()
    {
        View::render('development.index');
    }

}
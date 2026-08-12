<?php

namespace app\controllers;

use zap\http\Controller;

class NotFoundController extends Controller
{
    public function index()
    {
        http_response_code(404);
        view('404');
    }
}
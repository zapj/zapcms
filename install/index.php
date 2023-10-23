<?php

require  '../vendor/autoload.php';

$app = new \zap\App(realpath('../'));

$action = $_GET['action'] ?? 'index';

if(function_exists($action)){
    config_set('config.theme',false);
    \zap\view\View::paths(realpath('views/'));
    call_user_func($action);
}else{
    exit('404 Page Not found');
}


function index(){
    $data = [];
    view("index",$data);
}

function check(){
    $data = [];
    view("check",$data);
}

function database(){
    $data = [];
    view("database",$data);
}

function done(){
    $data = [];
    view("done",$data);
}

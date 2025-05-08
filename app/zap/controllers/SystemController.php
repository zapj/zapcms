<?php

namespace app\zap\controllers;

use zap\cms\AdminController;
use zap\cms\Option;
use zap\http\Request;
use zap\http\Response;
use zap\view\View;

class SystemController extends AdminController
{
    function settings(){
        $keyPrefix = '^website\.';
        if(Request::isPost()){
            $options = Request::post('options',[]);
            $optionKeys = Option::getKeys($keyPrefix,'REGEXP');
            foreach ($options as $key=>$value){
                if(in_array($key,$optionKeys)){
                    Option::update($key,$value,null,1);
                }else{
                    Option::add($key,$value,0,1);
                }
            }
            Response::json(['code'=>0,'msg'=>'保存成功']);
        }
        $data = [
            'options'=> Option::getArray($keyPrefix,'REGEXP')
        ];
        View::render("system.settings",$data);
    }

    public function sysInfo()
    {
        View::render("system.sysinfo",[]);
    }

    public function database(){
        \view('system.database',[]);
    }


}
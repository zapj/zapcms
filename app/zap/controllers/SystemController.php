<?php

namespace app\zap\controllers;

use zap\AdminController;
use zap\facades\Url;
use zap\http\Request;
use zap\http\Response;
use zap\Option;
use zap\util\Password;
use zap\view\View;

class SystemController extends AdminController
{
    function settings(){
        if(Request::isPost()){
            $options = Request::post('options',[]);
            $options_keys = array_keys($options);
            $keys = array_unique(array_map(function($key){return explode('.', $key)[0];},$options_keys));
            $regexKeys = array_map(function($key){
                return "^{$key}\.";
            },$keys);
            if(count($regexKeys)){
                $optionKeys = Option::getKeys(join('|',$regexKeys),'REGEXP');
            }else{
                $optionKeys = [];
            }
            foreach ($options as $key=>$value){
                if(in_array($key,$optionKeys)){
                    Option::update($key,$value);
                }else{
                    Option::add($key,$value);
                }
            }
            Response::json(['code'=>0,'msg'=>'保存成功']);
        }
        $data = [
            'options'=> Option::getArray('^website\.','REGEXP')
        ];
        View::render("system.settings",$data);
    }

}
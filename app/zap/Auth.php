<?php

namespace app\zap;


use zap\facades\Url;
use zap\http\Response;

class Auth
{
    public static function isLogin(){

        if(session()->has('zap.admin')){
            return true;
        }
        return false;
    }

    public static function check(){
        if(!session()->has('zap.admin')){
            Response::redirect(Url::action('Auth@signIn'));
        }
    }
}
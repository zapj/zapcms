<?php
/*
 * Copyright (c) 2023.  ZAP.CN  - ZAP CMS - All Rights Reserved
 * @author Allen
 * @email zapcms@zap.cn
 * @date 2023/12/27 上午11:29
 * @lastModified 2023/12/27 上午11:20
 *
 */

namespace zap\cms;


use zap\facades\Url;
use zap\http\Request;
use zap\http\Response;

class Auth
{

    protected static $scope = 'zapAdmin';

    public static function isLogin(): bool
    {

        if(session()->has(static::$scope)){
            return true;
        }
        return false;
    }

    /**
     * @throws \Exception
     */
    public static function check($url = null,$message = null,$type = FLASH_INFO){
        if(!session()->has(static::$scope)){
            $url  = $url ?? Url::action('Auth@signIn');
            $message = $message ?? '未登录或已超时，请重新登录';
            if(Request::isAjax()){
                Response::json(['code'=>-1,'msg'=>$message,'type'=>$type]);
            }
//            $prevUrl = Request::prevUrl();
//            Response::redirect($url,$message,$type);
            Response::redirect($url);
        }
    }

    public static function user($key = null,$default = null){
        app()->has(static::$scope) ?: app()->set(static::$scope,session()->get(static::$scope));
        if($key){
            return app()->get(static::$scope)[$key] ?? $default;
        }
        return app()->get(static::$scope) ?? $default;
    }

    public static function signOut($scope = null){
        $scope = $scope ?? static::scope();
        session()->remove($scope);
    }

    public static function scope($scope = null): string
    {
        if($scope){
            static::$scope = $scope;
        }
        return static::$scope;
    }

    public static function login($data){

    }
}
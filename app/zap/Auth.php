<?php

namespace zap;


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
            if(Request::isAjax()){
                Response::json(['code'=>-1,'msg'=>'未登录或已超时，请重新登录']);
            }
            Response::redirect($url,$message,$type);
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

    public static function getProfile(){
        $id = static::user('id');
        return DB::table('admin')->where('id',$id)->fetch(FETCH_ASSOC);
    }

    public static function scope($scope = null): string
    {
        if($scope){
            static::$scope = $scope;
        }
        return static::$scope;
    }
}
<?php

namespace app\zap\controllers;


use zap\DB;
use zap\facades\Url;
use zap\http\Controller;
use zap\http\Request;
use zap\http\Response;
use zap\http\Session;
use zap\util\Password;
use zap\view\View;

class AuthController extends Controller
{
    function index()
    {
        Response::redirect(Url::action('Auth@signIn'));
    }

    function signIn()
    {
        if (\zap\http\Request::isPost()) {
            $username = Request::post('username');
            $password = Request::post('password');
            $admin = DB::table('admin')->where('username', $username)->fetch(FETCH_OBJ);
            if (is_null($admin) || ($admin && Password::verify($password, $admin->password) === false )) {
                Response::redirect(Url::action('Auth@signIn'), "登录失败，用户名或密码错误", Session::ERROR);
            }
            //登录成功
            DB::table('admin')->set('last_ip', Request::ip())->set('last_access_time', time())->update();
            session()->set('zap.admin',[
                'last_ip'=>$admin->last_ip,
                'last_access_time'=>$admin->last_access_time,
                'id'=>$admin->id,
                'username'=>$admin->username,
            ]);
            Response::redirect(Url::action('Index'), "登录成功", Session::SUCCESS);
        }
        View::render("login.login");
    }

    function signOut()
    {
        \session()->remove('zap.admin');
        Response::redirect(Url::action('Auth@signIn'), "您已安全退出", Session::SUCCESS);
    }


}
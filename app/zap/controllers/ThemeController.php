<?php

namespace app\zap\controllers;

use zap\AdminController;
use zap\Auth;
use zap\DB;
use zap\facades\Url;
use zap\helpers\Pagination;
use zap\http\Request;
use zap\http\Response;
use zap\User;
use zap\util\Password;
use zap\view\View;

class ThemeController extends AdminController
{

    public function index(){
        $pageHelper = new Pagination(intval(Request::get('page',1)),20, Request::get());
        $pageHelper->setTotal(User::count());
        $users = User::select()->get(FETCH_ASSOC);
        view('user.index',['pageHelper'=>$pageHelper,'users'=>$users]);
    }

    public function form(){

        view('user.form',[]);
    }


    function changePassword(){
        if(Request::isPost()){
            $curPassword = Request::post('cur_password');
            $newPassword = Request::post('new_password');
            $reNewPassword = Request::post('renew_password');
            if($newPassword != $reNewPassword){
                Response::json(['code'=>1,'msg'=>'新密码两次输入不一致']);
            }
            $admin = Auth::getProfile();
            if(!Password::verify($curPassword,$admin['password'])){
                Response::json(['code'=>1,'msg'=>'原密码输入错误，请重新输入！']);
            }
            DB::update('admin',['password'=>Password::hash($newPassword),'updated_at'=>time()],
                ['id'=>Auth::user('id')]);
            Auth::signOut();
            Response::json(['code'=>0,'msg'=>'密码修改成功，请重新登录']);
        }
        $data = [];
        View::render("user.change_password",$data);
    }

}
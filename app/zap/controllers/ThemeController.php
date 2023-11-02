<?php

namespace app\zap\controllers;

use zap\AdminController;
use zap\Auth;
use zap\DB;
use zap\facades\Url;
use zap\helpers\Pagination;
use zap\http\Request;
use zap\http\Response;
use zap\Option;
use zap\Theme;
use zap\User;
use zap\util\Password;
use zap\view\View;

class ThemeController extends AdminController
{

    public function index(){
        config_set('cache.status','disabled');
        $themes = Theme::instance()->getAllThemesInfo();
        $website_options = Option::getArray('website','REGEXP');
        view('theme.index',['themes'=>$themes,'website_options'=>$website_options]);
    }

    public function form(){

        view('user.form',[]);
    }

    public function activationTheme(){
        if(Request::isPost()){
            $theme = trim(req()->post('theme'));
            if(preg_match('/^[a-z0-9]{1,255}$/i',$theme) === false){
                Response::json(['code'=>1,'msg'=>'主题名字不合法']);
            }
            if(!is_dir(themes_path($theme))){
                Response::json(['code'=>1,'msg'=>'主题不存在']);
            }
            Option::update('website.theme',$theme);
            Response::json(['code'=>0,'msg'=>'主题设置成功']);
        }
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
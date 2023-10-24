<?php

namespace app\zap\controllers;

use zap\Admin;
use zap\AdminController;
use zap\AdminMenu;
use zap\Auth;
use zap\DB;
use zap\facades\Url;
use zap\helpers\Pagination;
use zap\http\Request;
use zap\http\Response;
use zap\Log;
use zap\rbac\AdminRoles;
use zap\rbac\Permissions;
use zap\rbac\Roles;
use zap\rbac\RolesPermissions;
use zap\User;
use zap\util\Password;
use zap\view\View;

class UserController extends AdminController
{

    public function index(){
        $pageHelper = new Pagination(intval(Request::get('page',1)),20, Request::get());
        $pageHelper->setTotal(Admin::count());
        $users = Admin::select()->orderBy('id DESC')->get(FETCH_ASSOC);
        view('user.index',['pageHelper'=>$pageHelper,'users'=>$users]);
    }

    public function form(){
        $id = intval(Request::get('id'));
        AdminRoles::create([
            'admin_id'=>1,
            'role_id'=>2,
            'assignment_time'=>time()
        ]);
        view('user.form',[
            'user'=>Admin::getProfile($id),
            'roles'=>Roles::findAll(),
            'user_roles'=>AdminRoles::select('role_id')->where('admin_id',$id)->get(FETCH_COLUMN)
        ]);
    }

    public function saveUser(){
        if(IS_AJAX && req()->isPost()){
            $data = Request::post('data',[]);
            $user_roles = Request::post('user_roles',[]);
            $admin_id = intval(Request::post('admin_id'));
            if(strlen($data['password']) >= 6 && $data['password'] == $data['new_password']){
                $data['password'] = Password::hash($data['password']);
            }else{
                unset($data['password']);
            }
            unset($data['new_password']);
//            if(Auth::user('id') == 1){
            if($admin_id == 1){
                unset($data['status']);
            }
            $admin = Admin::findByUsername($data['username'])->fetch(FETCH_ASSOC);
            if(($admin && $admin_id == 0)
            || ($admin && Admin::getProfile($admin_id)['username']!=$data['username'])){
                response(['code'=>1,'msg'=>'用户名已存在'.$admin->username])->withJson();
            }
            if($admin_id){
                $data['updated_at'] = time();
                Admin::updateAll($data,['id'=>$admin_id]);
                AdminRoles::delete(['admin_id'=>$admin_id]);
            }else{
                $data['created_at'] = $data['updated_at'] = time();
                $admin_id = Admin::create($data)->id;
            }

            foreach ($user_roles as $role_id){
//                AdminRoles::create([
//                    'admin_id'=>$admin_id,
//                    'role_id'=>$role_id,
//                    'assignment_time'=>time()
//                ]);
                DB::insert('admin_roles',[
                    'admin_id'=>$admin_id,
                    'role_id'=>$role_id,
                    'assignment_time'=>time()
                ]);

            }
            response(['code'=>0,'msg'=>'保存成功'])->withJson();
        }
    }

    public function remove(){
        $data = Request::post('admin',[]);

        $admin_ids = array_unique(array_column($data,'id'));
        if(in_array(1,$admin_ids)){
            Response::json(['code'=>1,'msg'=>'超级管理员不能删除']);
        }
        if(count($admin_ids) < 1){
            Response::json(['code'=>1,'msg'=>'请选择需要删除的角色']);
        }
        Admin::where('id','IN',$admin_ids)->delete();
        AdminRoles::where('admin_id','IN',$admin_ids)->delete();
        Response::json(['code'=>0,'msg'=>'用户删除成功']);
    }


    public function profile(){
        view('user.profile',[]);
    }

    //roles
    public function roles(){
        $pageHelper = new Pagination(intval(Request::get('page',1)),20, Request::get());
        $pageHelper->setTotal(Roles::count());
        $data = Roles::select()->orderBy("role_id DESC")->get(FETCH_ASSOC);
        view('user.roles',['pageHelper'=>$pageHelper,'data'=>$data]);
    }

    public function formRole(){
        $id = intval(req()->get('id'));
        if($id){
            $role = Roles::findById($id,FETCH_ASSOC);
        }
        $permissions = Permissions::instance()->getAllByPath([]);

        view('user.roles_form',[
            'data'=>$role ?? [],
            'permissions'=> $permissions,
            'role_permissions'=> RolesPermissions::getByRoleId($id),
            'admin_menus' => AdminMenu::instance()->getAllByPath([])
        ]);
    }

    public function saveRole(){
        if(IS_AJAX && req()->isPost()){
            $data = Request::post('data',[]);
            $perms = Request::post('perms',[]);
            $extras = Request::post('extras',[]);
            $role_id = intval(Request::post('role_id'));
            if($role_id){
                $data['updated_at'] = time();
                Roles::updateAll($data,['role_id'=>$role_id]);
                RolesPermissions::delete(['role_id'=>$role_id]);
            }else{
                $data['created_at'] = $data['updated_at'] = time();
                $role_id = Roles::create($data)->role_id;
            }
            $stm = DB::prepare('insert into {roles_permissions} values (:role_id,:perm_key,:extras,:assignment_time)');
            foreach ($perms as $key){

                $stm->execute([
                    'role_id'=>$role_id,
                    'perm_key'=>$key,
                    'extras'=> isset($extras[$key]) ? join(',',$extras[$key]) : '',
                    'assignment_time'=>time()
                ]);
            }
            response(['code'=>0,'msg'=>'保存成功'])->withJson();
        }
    }

    public function removeRole(){
        $data = Request::post('data',[]);

        $role_ids = array_unique(array_column($data,'role_id'));
        if(in_array(1,$role_ids)){
            Response::json(['code'=>1,'msg'=>'超级管理员不能删除']);
        }
        if(count($role_ids) < 1){
            Response::json(['code'=>1,'msg'=>'请选择需要删除的角色']);
        }
        Roles::where('role_id','IN',$role_ids)->delete();
        RolesPermissions::where('role_id','IN',$role_ids)->delete();
        Response::json(['code'=>0,'msg'=>'角色删除成功']);
    }

    //permissions
    public function permissions(){
        $pageHelper = new Pagination(intval(Request::get('page',1)),20, Request::get());
        $pageHelper->setTotal(Permissions::instance()->getAllByPath([
            'count'=>true
        ])
        );
        $data = Permissions::instance()->getAllByPath([
            'limit'=>[$pageHelper->getLimit(),$pageHelper->getOffset()]
        ]);
        view('user.permissions',['pageHelper'=>$pageHelper,'data'=>$data]);
    }

    public function formPermission(){
        $id = intval(req()->get('id'));
        $pid = intval(req()->get('pid'));
        if($id){
            $permission = Permissions::instance()->get($id);
        }
        view('user.permissions_form',[
            'data'=>$permission ?? [],
            'extras'=> $permission['extras'] ? json_decode($permission['extras'],true) : [],
            'pid'=>$pid
        ]);
    }

    public function savePermission(){
        if(IS_AJAX && req()->isPost()){
            $data = Request::post('data',[]);
            $id = intval(Request::post('perm_id'));
            $extras = [];
            foreach (Request::post('extras') as $extra){
                $extras[$extra['key']] = $extra['title'];
            }
            $data['extras'] = json_encode($extras,JSON_UNESCAPED_UNICODE);
            if(json_last_error() !== JSON_ERROR_NONE){
                $data['extras'] = '[]';
            }
            if($id){
                $data['updated_at'] = time();
                Permissions::instance()->update($data,$id);
            }else{
                $data['created_at'] = $data['updated_at'] = time();
                Permissions::instance()->add($data);
            }

            response(['code'=>0,'msg'=>'保存成功'])->withJson();
        }
    }

    public function removePermission(){
        $data = Request::post('data',[]);

        $perm_ids = array_unique(array_column($data,'perm_id'));

        if(count($perm_ids) < 1){
            Response::json(['code'=>1,'msg'=>'请选择需要删除的权限名称']);
        }
        foreach ($perm_ids as $id){
            Permissions::instance()->remove($id);
        }
        Response::json(['code'=>0,'msg'=>'权限删除成功']);
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
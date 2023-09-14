<?php

namespace app\zap\controllers;

use zap\AdminMenu;
use zap\AdminController;
use zap\Catalog;
use zap\DB;
use zap\http\Request;
use zap\http\Response;
use zap\view\View;

/*
 * 栏目
 */
class AdminMenuController extends AdminController
{
    function index(){
        $data = [];
        $data['menu'] = AdminMenu::instance();
        View::render("admin_menu.index",$data);
    }

    public function save()
    {
        $catalog = Request::post('admin_menu',[]);
        foreach ($catalog as $id=>$row){
            DB::update('admin_menu',$row,['id'=>$id]);
        }
        Response::json(['code'=>0,'msg'=>'保存成功']);
    }

    public function saveAdminMenu(){
        if(Request::isPost()){
            $data = Request::post('zap_data',[]);
            if(empty($data['icon'])){
                $data['icon'] = 'fa fa-circle-notch';
            }
            AdminMenu::instance()->add($data,$data['pid']);
            Response::json(['code'=>0,'msg'=>'保存成功']);
        }
    }

    public function remove()
    {
        $catalog = Request::post('admin_menu',[]);
        $menu = AdminMenu::instance();
        foreach ($catalog as $id=>$row){
            $menu->remove($id);
        }
        Response::json(['code'=>0,'msg'=>'系统菜单删除成功']);
    }

    public function form()
    {

        $data['pid'] = intval(Request::get('pid',0));
        if($data['pid']){
            $data['parent'] = AdminMenu::instance()->get($data['pid']);
        }
//        $data['catalog'] = Catalog::instance()->getTreeArray();
//        print_r($data['parent']);
//        echo ltrim($data['parent']['path'],'0,');

        View::render('admin_menu.form',$data);
    }

}
<?php

namespace app\zap\controllers;

use zap\AdminController;
use zap\Catalog;
use zap\DB;
use zap\http\Request;
use zap\http\Response;
use zap\view\View;

/*
 * 栏目
 */
class CatalogController extends AdminController
{
    function index(){
//        Asset::library('summernote');
        $data = [];
        $menu = new Catalog();
        $data['menu'] = $menu;
        View::render("catalog.index",$data);
    }

    public function save()
    {
        $catalog = Request::post('catalog',[]);
        foreach ($catalog as $id=>$row){
            DB::update('catalog',$row,['id'=>$id]);
        }
        Response::json(['code'=>0,'msg'=>'保存成功']);
    }

    public function saveCatalog(){
        $catalog = Request::post('catalog',[]);
        $menu = new Catalog();
        foreach ($catalog as $row){
//            if(!isset($row['pid'])){
//                $row['pid'] = 0;
//            }
            $menu->add($row,$row['pid']);
        }
        Response::json(['code'=>0,'msg'=>'保存成功']);
    }

    public function remove()
    {
        $catalog = Request::post('catalog',[]);
        $menu = new Catalog();
        foreach ($catalog as $id=>$row){
            $menu->remove($id);
        }
        Response::json(['code'=>0,'msg'=>'分类删除成功']);
    }

    public function form()
    {

        $data['pid'] = intval(Request::get('pid',0));
        if($data['pid']){
            $data['parent'] = Catalog::instance()->get($data['pid']);
        }
//        $data['catalog'] = Catalog::instance()->getTreeArray();
//        print_r($data['parent']);
//        echo ltrim($data['parent']['path'],'0,');

        View::render('catalog.form',$data);
    }

}
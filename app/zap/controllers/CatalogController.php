<?php

namespace app\zap\controllers;

use zap\AdminController;
use zap\Catalog;
use zap\DB;
use zap\http\Request;
use zap\http\Response;
use zap\util\Str;
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
        $catalogId = intval(Request::post('catalog_id',0));

        $catalog['seo_name'] = Str::slug(empty($catalog['seo_name']) ? $catalog['title'] : $catalog['seo_name']);
        $catalog['show_position'] = join(',', $catalog['show_position']);
        $menu = new Catalog();
        if($catalogId){
            $menu->update($catalog,$catalogId);
        }else{
            $menu->add($catalog,$catalog['pid']);
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
        $data['cid'] = intval(Request::get('cid',0));
        if($data['pid']){
            $data['parent'] = Catalog::instance()->get($data['pid']);
        }

        $data['catalog'] = $data['cid'] == 0 ? [] : Catalog::instance()->get($data['cid']);

        View::render('catalog.form',$data);
    }

}
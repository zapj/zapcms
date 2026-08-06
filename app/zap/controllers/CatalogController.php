<?php

namespace app\zap\controllers;

use zap\cms\AdminController;
use zap\cms\Catalog;
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
        $data = [];
        $menu = Catalog::instance();
        $data['menu'] = $menu;
        $page_title = '栏目管理';
        $breadcrumbs = [
            ['title' => '首页', 'url' => url_action('Index')],
            ['title' => '栏目管理', 'url' => url_action('Catalog')],
        ];
        $data['page_title'] = $page_title;
        $data['breadcrumbs'] = $breadcrumbs;
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
        if($catalog['node_type'] == 'link-url'){
            $catalog['slug'] = '--zap-link-url';
        }else{
            $catalog['slug'] = Str::slug(empty($catalog['slug']) ? $catalog['title'] : $catalog['slug']);
        }

        $catalog['show_position'] = join(',', $catalog['show_position'] ?? []);
        $menu = Catalog::instance();
        if($catalogId){
            $menu->update($catalog,$catalogId);
        }else{
            $menu->add($catalog);
        }

        Response::json(['code'=>0,'msg'=>'保存成功']);
    }

    public function remove()
    {
        $catalog = Request::post('catalog',[]);
        $menu = Catalog::instance();
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
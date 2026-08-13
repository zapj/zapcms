<?php
/*
 * Copyright (c) 2023.  ZAP.CN  - ZAP CMS
 */

namespace app\controllers;

use zapcms\services\BreadCrumb;
use zapcms\services\Catalog;
use zap\DB;
use zap\exception\ViewNotFoundException;
use zap\helpers\Pagination;
use zap\http\Controller;
use zap\view\View;

class CatalogController extends Controller
{
    public function __construct()
    {
        BreadCrumb::instance()->add('首页',base_url('/'));
    }

    public function _invoke(string $method, $params = [])
    {
        if(method_exists($this,$method)){
            $this->$method();
        }else{
            $this->index();
        }
    }

    function index(){
        $firstSegment = current($this->params);
//        echo pageState()->nodeType;
//        echo pageState()->nodeMimeType;
        pageState()->getCatalog();

        pageState()->catalogPaths = Catalog::instance()->getCatalogPathById(pageState()->nodeId);
        $lastKey = array_key_last(pageState()->catalogPaths );
        foreach (pageState()->catalogPaths as $key => $catalog){
            BreadCrumb::instance()->add($catalog['title'],site_url("/{$catalog['slug']}"),$key === $lastKey);
        }

        pageState()->subCatalogList = Catalog::instance()->getSubCatalogList(pageState()->catalog['pid'] == 0 ? pageState()->catalog['id'] : pageState()->catalog['pid']);
        try{
            if(pageState()->nodeMimeType==='page') {
                view('page', ['node' => pageState()->node]);
                return;
            }else{
                $page = new Pagination(intval(req()->get('page',1)),12,req()->get());
                $view = View::make( theme_file_is_exists(pageState()->nodeMimeType . '_list') ? pageState()->nodeMimeType.'_list' : pageState()->nodeMimeType);
                $query = DB::table('node_relation','nr')->leftJoin(['node','n'],'nr.node_id=n.id')
                    ->where('nr.catalog_id',pageState()->nodeId)
                    ->where('n.node_type', pageState()->nodeMimeType);
                $view->page = $page->setTotal($query->count('n.id'));
                $query->limit($page->getLimit())->offset($page->getOffset());
                $view->nodes = $query->get(FETCH_ASSOC);
                $view->page = $page;
                //// 模板页面没有相关联的栏目菜单时，读取左侧导航栏目菜单作为兜底（侧边栏位于页面左侧）
                if (empty(pageState()->subCatalogList) && !empty($view->nodes)) {
                    pageState()->subCatalogList = \zapcms\services\Catalog::instance()->getPositionMenu(\zapcms\services\Catalog::POSITION_LEFT);
                }
            }

        }catch (ViewNotFoundException $exception){
            $view = View::make(pageState()->nodeType);
        }
        $view->display();
    }


}
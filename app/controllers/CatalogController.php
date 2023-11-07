<?php
/*
 * Copyright (c) 2023.  ZAP.CN  - ZAP CMS
 */

namespace app\controllers;

use zap\BreadCrumb;
use zap\Catalog;
use zap\DB;
use zap\exception\ViewNotFoundException;
use zap\helpers\Pagination;
use zap\http\Controller;
use zap\Node;
use zap\NodeRelation;
use zap\view\View;

class CatalogController extends Controller
{
    public function __construct()
    {
        BreadCrumb::instance()->add('首页',base_url('/'));
    }

    public function _invoke($method,$params = [])
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
                $view = View::make(pageState()->nodeMimeType);
            }else{
                $page = new Pagination(intval(req()->get('page',1)),12,req()->get());
                $view = View::make( theme_file_is_exists(pageState()->nodeMimeType . '_list') ? pageState()->nodeMimeType.'_list' : pageState()->nodeMimeType);
                $query = DB::table('node_relation','nr')->leftJoin(['node','n'],'nr.node_id=n.id')
                    ->where('nr.catalog_id',pageState()->nodeId);
                $view->page = $page->setTotal($query->count('n.id'));
                $query->limit($page->getLimit(),$page->getOffset());
                $view->data_list = $query->get(FETCH_ASSOC);

            }

        }catch (ViewNotFoundException $exception){
            $view = View::make(pageState()->nodeType);
        }
        $view->display();
    }


}
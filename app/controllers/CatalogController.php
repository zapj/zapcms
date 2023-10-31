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
//        echo page()->nodeType;
//        echo page()->nodeMimeType;
        page()->getCatalog();

        page()->catalogPaths = Catalog::instance()->getCatalogPathById(page()->nodeId);
        $lastKey = array_key_last(page()->catalogPaths );
        foreach (page()->catalogPaths as $key => $catalog){
            BreadCrumb::instance()->add($catalog['title'],site_url("/{$catalog['slug']}"),$key === $lastKey);
        }

        page()->subCatalogList = Catalog::instance()->getSubCatalogList(page()->catalog['pid'] == 0 ? page()->catalog['id'] : page()->catalog['pid']);
        try{
            if(page()->nodeMimeType==='page') {
                $view = View::make(page()->nodeMimeType);
            }else{
                $view = View::make( theme_file_is_exists(page()->nodeMimeType . '_list') ? page()->nodeMimeType.'_list' : page()->nodeMimeType);
                $query = DB::table('node_relation','nr')->leftJoin(['node','n'],'nr.node_id=n.id')
                    ->where('nr.catalog_id',page()->nodeId);
                $page = new Pagination(intval(req()->get('page',1)),20,req()->get());
                $view->page = $page->setTotal($query->count('n.id'));
                $view->data_list = $query->get(FETCH_ASSOC);

            }

        }catch (ViewNotFoundException $exception){
            $view = View::make(page()->nodeType);
        }
        $view->display();
    }


}
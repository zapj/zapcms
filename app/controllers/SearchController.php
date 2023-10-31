<?php
/*
 * Copyright (c) 2023.  ZAP.CN  - ZAP CMS
 */

namespace app\controllers;

use zap\BreadCrumb;
use zap\Catalog;
use zap\http\Controller;
use zap\Node;

class SearchController extends Controller
{
    public function __construct()
    {
        BreadCrumb::instance()->add('首页',base_url('/'));
        BreadCrumb::instance()->add('搜索','#',true);
    }

    function index(){
        $keyword = trim(req()->get('q'));
//        page()->catalogPaths = $this->getCatalogPathByNodeId(page()->nodeId);
//        $slugs = [];
//        foreach (page()->catalogPaths as $catalog){
//            $slugs[] = $catalog['slug'];
//            BreadCrumb::instance()->add($catalog['title'],site_url("/{$catalog['slug']}"));
//        }
//        $slug = page()->node['slug'];
//        BreadCrumb::instance()->add(page()->node['title'],site_url("/{$slug}"),true);

        //侧边栏菜单
//        $topCatalog = current(page()->catalogPaths);
//        page()->subCatalogList = Catalog::instance()->getSubCatalogList($topCatalog['id']);
        $data_list = Node::where('title','LIKE',"%{$keyword}%")
            ->where('status',Node::STATUS_PUBLISH)
            ->get(FETCH_ASSOC);

        view('search',[
            'data_list'=>$data_list
        ]);
    }
}
<?php
/*
 * Copyright (c) 2023.  ZAP.CN  - ZAP CMS
 */

namespace app\controllers;

use zap\BreadCrumb;
use zap\Catalog;
use zap\http\Controller;
use zap\NodeRelation;

class NodeController extends Controller
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
        pageState()->catalogPaths = $this->getCatalogPathByNodeId(pageState()->nodeId);
        $slugs = [];
        foreach (pageState()->catalogPaths as $catalog){
            $slugs[] = $catalog['slug'];
            BreadCrumb::instance()->add($catalog['title'],site_url("/{$catalog['slug']}"));
        }
        $slug = pageState()->node['slug'];
        BreadCrumb::instance()->add(pageState()->node['title'],site_url("/{$slug}"),true);

        //侧边栏菜单
        $topCatalog = current(pageState()->catalogPaths);
        pageState()->subCatalogList = Catalog::instance()->getSubCatalogList($topCatalog['id']);
        view('node',[]);
    }

    function product(){
        //获取 url path
        pageState()->catalogPaths = $this->getCatalogPathByNodeId(pageState()->nodeId);
        $slugs = [];
        foreach (pageState()->catalogPaths as $catalog){
            $slugs[] = $catalog['slug'];
            BreadCrumb::instance()->add($catalog['title'],site_url("/{$catalog['slug']}"));
        }
        $slug = pageState()->node['slug'];
        BreadCrumb::instance()->add(pageState()->node['title'],site_url("/{$slug}"),true);

        //侧边栏菜单
        $topCatalog = current(pageState()->catalogPaths);
        pageState()->subCatalogList = Catalog::instance()->getSubCatalogList($topCatalog['id']);
        view('product',[]);
    }

    private function getCatalogPathByNodeId($node_id){
        return NodeRelation::where('node_id',$node_id)->orderBy('level ASC')
            ->leftJoin(['node','n'],'node_relation.catalog_id=n.id')
            ->select('n.title,n.id,n.slug')
            ->get(FETCH_ASSOC);
    }
}
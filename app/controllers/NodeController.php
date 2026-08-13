<?php
/*
 * Copyright (c) 2023.  ZAP.CN  - ZAP CMS
 */

namespace app\controllers;

use zapcms\services\BreadCrumb;
use zapcms\services\Catalog;
use zapcms\models\Node;
use zapcms\models\NodeRelation;
use zap\http\Controller;

class NodeController extends Controller
{
    public function __construct()
    {
        BreadCrumb::instance()->add('首页',base_url('/'));
    }

    public function _invoke(string $method,$params = [])
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

    function page(){
        $node = pageState()->node;
        $slug = $node['slug'];
        BreadCrumb::instance()->add($node['title'],site_url("/{$slug}"),true);
        view('page', ['node' => $node]);
    }

    function article(){
        $node = pageState()->node;
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
        view('article', ['node' => $node]);
    }

    function product(){
        $node = pageState()->node;
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
        // 加载自定义字段（node_meta），如产品价格
        $nodeModel = Node::findById($node['id'] ?? 0);
        $node['meta'] = $nodeModel ? $nodeModel->loadMeta() : [];
        view('product', ['node' => $node]);
    }

    private function getCatalogPathByNodeId(int $node_id){
        return NodeRelation::where('node_id',$node_id)->orderBy('level ASC')
            ->leftJoin(['node','n'],'node_relation.catalog_id=n.id')
            ->select('n.title,n.id,n.slug')
            ->get(FETCH_ASSOC);
    }
}
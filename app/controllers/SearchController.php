<?php
/*
 * Copyright (c) 2023.  ZAP.CN  - ZAP CMS
 */

namespace app\controllers;

use app\PageState;
use zap\cms\BreadCrumb;
use zap\cms\Catalog;
use zap\cms\models\Node;
use zap\helpers\Pagination;
use zap\http\Controller;

class SearchController extends Controller
{
    public function __construct()
    {
        BreadCrumb::instance()->add('首页',base_url('/'));
        BreadCrumb::instance()->add('搜索','#',true);
    }

    function index(){
        $keyword = trim(req()->get('q'));

        $page = new Pagination(intval(req()->get('page',1)),12,req()->get());
        pageState()->subCatalogList = PageState::getSearchSidebarMenu();
        $query = Node::where('title','LIKE',"%{$keyword}%")
            ->where('status',Node::STATUS_PUBLISH)
            ->where('node_type','IN',['product','article','faq']);
        // set total
        $page->setTotal($query->count());
        // limit
        $query->limit($page->getLimit(),$page->getOffset());
        view('search',[
            'data_list'=> $query->get(FETCH_ASSOC),
            'page' => $page,
        ]);
    }
}
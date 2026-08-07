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
        $data['catalog_count'] = DB::table('catalog')->count();
        $page_title = '栏目管理';
        $breadcrumbs = [
            ['title' => '控制台', 'url' => url_action('Index')],
            ['title' => '栏目管理'],
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

    /**
     * 搜索可链接的内容（栏目 + 节点）
     *
     * @return void JSON
     */
    public function searchLinkTarget()
    {
        $keyword = trim((string) Request::get('keyword', ''));
        $limit   = min(50, max(1, intval(Request::get('limit', 30))));

        // 搜索栏目
        $catalogs = [];
        Catalog::instance()->forEachAll(function ($row) use (&$catalogs, $keyword) {
            if ($keyword !== '' && stripos($row['title'], $keyword) === false) {
                return;
            }
            $catalogs[] = [
                'id'         => (int) $row['id'],
                'title'      => $row['title'],
                'node_type'  => $row['node_type'] ?: 'catalog',
                'mime_type'  => $row['mime_type'] ?? '',
                'kind'       => 'catalog',
                'path_label' => str_repeat('— ', max(0, (int) $row['level'] - 1)) . $row['title'],
            ];
        });

        // 搜索节点（排除 catalog 类型，因为 catalog 已在上方列出）
        $nodeQuery = \zap\cms\models\Node::createQuery()
            ->whereNotIn('node_type', ['catalog']);
        if ($keyword !== '') {
            $nodeQuery->where('title', 'LIKE', "%{$keyword}%");
        }
        $nodes = $nodeQuery->orderBy('id DESC')->limit($limit)->get(FETCH_ASSOC);
        $nodes = $nodes ?: [];
        foreach ($nodes as &$node) {
            $node['kind']       = 'node';
            $node['path_label'] = $node['title'];
        }
        unset($node);

        Response::json([
            'code'      => 0,
            'catalogs'  => $catalogs,
            'nodes'     => $nodes,
            'total'     => count($catalogs) + count($nodes),
        ]);
    }
}
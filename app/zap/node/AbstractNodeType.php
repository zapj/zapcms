<?php

namespace zap\node;

use zap\cms\BreadCrumb;
use zap\cms\Catalog;
use zap\cms\HtmlXss;
use zap\cms\models\Node;
use zap\cms\models\NodeRelation;
use zap\DB;
use zap\db\Query;
use zap\exception\ViewNotFoundException;
use zap\helpers\Pagination;
use zap\http\Request;
use zap\http\Response;
use zap\http\Router;
use zap\util\Arr;
use zap\util\Str;
use zap\view\View;

class AbstractNodeType
{
    protected $nodeType;

    protected $catalogId;
    protected $nodeId;

    public $controller;
    public $action;

    protected $title;

    protected $isAjax;

    public function __construct()
    {
        $this->catalogId = intval(Request::get('cid'));
        $this->isAjax = Request::isAjax();
        BreadCrumb::instance()->add('内容管理',url_action('Node'));
    }

    public function __init(){

    }

    //controller actions
    public function index(){
        $data['title'] = $this->getTitle("%s管理");
        $conditions = [
            'where'=>[
                'n.node_type'=>$this->nodeType,
//                'n.author_id'=>Auth::user('id'),
            ]
        ];
        $this->catalogId && $conditions['where']['nr.catalog_id']= $this->catalogId;
        $conditions = apply_filters('node_total_conditions',$conditions);
        $page = $this->usePageHelper($this->getTotalRows($conditions));
        $conditions['orderBy'] = 'id desc';
        $conditions['limit'] = [$page->getLimit(),$page->getOffset()];
        $conditions = apply_filters('node_get_all_conditions',$conditions);
        $data['data'] = $this->getAll($conditions);
        $this->display($data);
    }

    public function edit($id = 0){
        $id = intval($id);
        if(!$id){
            $this->redirectTo("Node@{$this->action}",$_GET,$this->getTitle("%s不存在"),FLASH_ERROR);
        }
        if(Request::isPost()){
            $node = Request::post('node');
            $catalogArray = Request::post('catalog',[]);
            $node['update_time'] = time();
            $node['pub_time'] = strtotime($node['pub_time']) ?: time();
            $node['content'] = HtmlXss::clean($node['content']);
//            $node['slug'] = Str::slug($node['title']);
            NodeRelation::delete(['node_id'=>$id]);
            foreach ($catalogArray as $catalog_id=>$level){
                NodeRelation::create(['node_id'=>$id,'catalog_id'=>$catalog_id,'level'=>$level]);
            }
            Node::updateAll($node,['id'=>$id]);
            Response::json(['code'=>0,'msg'=>$this->getTitle("%s修改成功"),'id'=>$id]);
        }
        $this->nodeId = $id;
        $data['title'] = $this->title;
        $data['sub_title'] = $this->getTitle("修改%s");
        $data['node'] = Node::findById($id);
        $data['node_relations'] = $this->getNodeRelationships($id);
        $data['catalogList'] = Catalog::instance()->getTreeArray(['node_type'=>$data['node']['node_type']]);
        $this->display($data,'form');
    }

    public function add()
    {
        if(Request::isPost()){
            $node = Request::post('node');
            $catalogArray = Request::post('catalog',[]);
            $node['node_type'] = $this->nodeType;
            $node['add_time'] = time();
            $node['slug'] = Str::slug($node['title']);
            $node['update_time'] = time();
            $node['content'] = HtmlXss::clean($node['content']);
            $node['pub_time']  = strtotime($node['pub_time']) ?: time();
            $node = apply_filters('node_add',$node);
            $nodeModel = Node::create($node);
            foreach ($catalogArray as $catalog_id=>$level){
                NodeRelation::create(['node_id'=>$nodeModel->id,'catalog_id'=>$catalog_id,'level'=>$level]);
            }
            Response::json(['code'=>0,'msg'=> $this->title . '创建成功','id'=>$nodeModel->id,'redirect_to'=>url_action("Node@{$this->controller}/edit/{$nodeModel->id}",$_GET)]);

        }
        $data['title'] = $this->title;
        $data['sub_title'] = $this->getTitle("添加%s");
        $data['node'] = new Node();
        $data['node_relations'] = [];
        $data['catalogList'] = Catalog::instance()->getTreeArray(['node_type'=>$this->nodeType]);
        $this->display($data,'form');
    }

    function remove(){
        $id = intval(Request::post('id'));
        if(Request::isPost() && $id){
            $affId = Node::delete($id);
            if($affId){
                add_flash($this->title . '删除成功',FLASH_SUCCESS);
                Response::json(['code'=>0,'msg'=>$this->title . '删除成功']);
            }else{
                Response::json(['code'=>1,'msg'=>$this->title . '删除失败，ID不存在']);
            }
        }
        Response::json(['code'=>1,'msg'=>$this->title . '删除失败，ID不存在']);
    }



    protected function display($data = [], $name = null){

        $controller = Router::convertToUrlName($this->controller);
        $action = Router::convertToUrlName($this->action);
        $data['_controller'] = $this->controller;
        $data['_action'] = $this->action;
        $data['catalogId'] = $this->catalogId;
        $data['modTitle'] = $this->title;
        $data['catalogPaths'] = $this->getCurrentCatalogPath();
//        print_r(Catalog::instance()->getCatalogPathById($this->catalogId));
//        print_r( $data['catalogPaths'] );die;
        if($data['catalogPaths']){
            $lastKey = array_key_last($data['catalogPaths']);
            foreach ($data['catalogPaths'] as $key => $catalog){
                BreadCrumb::instance()->add($catalog['title'],url_action("Node@{$this->controller}",req()->get()), $lastKey == $key);
            }
        }else{
            //获取catalog path

        }
        try{
            View::render("{$controller}.". ($name ?? $action),$data);
        }catch (ViewNotFoundException $e){
            View::render("default.". ($name ?? $action),$data);
        }

    }

    protected function getCatalogById($id)
    {
        return Catalog::instance()->get($id);
    }

    protected function getCurrentCatalogPath(){
        if(!$this->catalogId){
            $this->catalogId = NodeRelation::find(['node_id'=>$this->nodeId])
                ->select('catalog_id')->orderBy('catalog_id desc')->fetchColumn();
        }
        return Catalog::instance()->getCatalogPathById($this->catalogId);
    }

    protected function usePageHelper($total,$pageKeyName = 'page',$limit = 20 , $query = null): Pagination
    {
        $this->pageHelper = new Pagination(intval(Request::get($pageKeyName,1)),$limit, $query ?? Request::get());
        $this->pageHelper->setTotal($total);
        View::share('page',$this->pageHelper);
        return $this->pageHelper;
    }

    protected function getTotalRows($conditions)
    {
        if($this->catalogId){
            $query = DB::table('node_relation','nr')
                ->leftJoin(['node','n'],'nr.node_id=n.id')
                ->select('count(n.id) as rowcount');
        }else{
            $query = DB::table('node','n')->select('count(n.id) as rowcount');
//            $query->where('author_id','!=',0);
        }

        $this->prepareConditions($query,$conditions);
        return $query->fetchColumn();
    }

    protected function getAll($conditions)
    {
        if($this->catalogId){
            $query = DB::table('node_relation','nr')
                ->leftJoin(['node','n'],'nr.node_id=n.id');
        }else{
            $query = DB::table('node','n');
//            $query->where('author_id','!=',0);
        }
        $query->select('n.*');
        $this->prepareConditions($query,$conditions);
       return $query->get(FETCH_ASSOC);
    }

    protected function getNodeRelationships($node_id){
        return NodeRelation::find(['node_id'=>$node_id])
            ->select('catalog_id,node_id')
            ->get(FETCH_KEY_PAIR);
    }

    protected function getNodeByCatalogId($catalog_id){
        return NodeRelation::createQuery()
            ->leftJoin('node','node.id=node_relation.node_id')
            ->where('node_relation.catalog_id',$catalog_id)
            ->fetch(FETCH_ASSOC);
    }

    protected function prepareConditions(Query $query,$conditions){
        foreach (Arr::get($conditions,'where',[]) as $name=>$value){
            if(is_int($name)){
                $query->where(...$value);
            }else{
                $query->where($name,$value);
            }
        }
        empty($conditions['orderBy']) or $query->orderBy($conditions['orderBy']);
        if(!empty($conditions['limit'])){
            if(is_int($conditions['limit'])) $conditions['limit'] = [$conditions['limit']];
            $query->limit(...$conditions['limit']);
        }
    }

    protected function redirectTo($action,$query = null,$message = NULL,$flashType = FLASH_INFO){
        if($this->isAjax){
            Response::json(['code'=> $flashType == FLASH_SUCCESS ? 0:-1 ,'msg'=>$message,'type'=>$flashType]);
        }
        Response::redirect(url_action($action,$query),$message,$flashType);
    }


    public function getNodeType(): int
    {
        return $this->nodeType;
    }

    public function setNodeType(string $nodeType): void
    {
        $this->nodeType = $nodeType;
    }

    public function getCatalogId(): int
    {
        return $this->catalogId;
    }

    public function setCatalogId(int $catalogId): void
    {
        $this->catalogId = $catalogId;
    }

    public function getTitle($msg = null): string
    {
        return $msg ? sprintf($msg,$this->title) : $this->title;
    }

    public function setTitle($title)
    {
        $this->title = $title;
    }

}
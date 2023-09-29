<?php

namespace zap\node;

use zap\Auth;
use zap\Catalog;
use zap\DB;
use zap\db\Query;
use zap\exception\ViewNotFoundException;
use zap\helpers\Pagination;
use zap\http\Request;
use zap\http\Response;
use zap\Node;
use zap\NodeRelation;
use zap\NodeType;
use zap\util\Arr;
use zap\util\Str;
use zap\view\View;

class AbstractType
{
    protected $nodeType = NodeType::NEWS;

    protected $catalogId;

    public $controller;
    public $action;

    protected $title;
    protected $isAjax;

    public function __construct()
    {
        $this->catalogId = intval(Request::get('catalog_id'));
        $this->isAjax = Request::isAjax();
    }

    public function __init(){

    }

    //controller actions
    public function index(){
        $data['catalogPaths'] = $this->getCurrentCatalogPath();
        $data['title'] = $this->getTitle("%s管理");

        $conditions = [
            'where'=>[
                'nr.catalog_id'=>$this->catalogId,
                'n.node_type'=>$this->nodeType,
                'n.author_id'=>Auth::user('id'),
            ]
        ];
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
            $this->redirectTo("Zap@{$this->action}",$_GET,$this->getTitle("%s不存在"),FLASH_ERROR);
        }
        if(Request::isPost()){
            $node = Request::post('node');
            $catalogArray = Request::post('catalog',[]);
            $node['update_time'] = time();
            $node['pub_time'] = strtotime($node['pub_time']) ?: time();
            NodeRelation::delete(['node_id'=>$id]);
            foreach ($catalogArray as $catalog_id){
                NodeRelation::create(['node_id'=>$id,'catalog_id'=>$catalog_id,'node_type'=>$this->nodeType]);
            }
            Node::updateAll($node,['id'=>$id]);
            Response::json(['code'=>0,'msg'=>$this->getTitle("%s修改成功"),'id'=>$id]);
            return;
        }
        $data['title'] = $this->title;
        $data['sub_title'] = $this->getTitle("修改%s");
        $data['node'] = Node::findById($id);
        $catalog = Catalog::instance()->get($this->catalogId);
        $data['node_relations'] = $this->getNodeRelationships($id);
        $data['catalogList'] = Catalog::instance()->getTreeArray(['node_type'=>$catalog['node_type']]);
        $this->display($data,'form');
    }

    public function add()
    {
        if(Request::isPost()){
            $node = Request::post('node');
            $catalogArray = Request::post('catalog',[]);
            $node['node_type'] = $this->nodeType;
            $node['add_time'] = time();
            $node['update_time'] = time();
            $node['pub_time']  = strtotime($node['pub_time']) ?: time();
            $node = apply_filters('node_add',$node);
            $nodeModel = Node::create($node);
            foreach ($catalogArray as $catalog_id){
                NodeRelation::create(['node_id'=>$nodeModel->id,'catalog_id'=>$catalog_id,'node_type'=>$this->nodeType]);
            }
            Response::json(['code'=>0,'msg'=> $this->title . '创建成功','id'=>$nodeModel->id,'redirect_to'=>url_action("Zap@{$this->controller}/edit/{$nodeModel->id}",$_GET)]);

        }
        $data['title'] = $this->title;
        $data['sub_title'] = $this->getTitle("添加%s");
        $data['node'] = new Node();
        $catalog = $this->getCatalogById($this->catalogId);
        $data['node_relations'] = [];
        $data['catalogList'] = Catalog::instance()->getTreeArray(['node_type'=>$catalog['node_type']]);
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

    protected function getTitle($msg): string
    {
        return sprintf($msg,$this->title);
    }

    protected function display($data = [], $name = null){
        $controller = strtolower(trim(preg_replace('/([A-Z])/', '-$1', $this->controller),'-'));
        $action = strtolower(trim(preg_replace('/([A-Z])/', '-$1', $this->action),'-'));
//        if(is_null($name)){
//            $name = "{$controller}.{$action}";
//        }else{
//            $name = "{$controller}.{$name}";
//        }
        $data['_controller'] = $this->controller;
        $data['_action'] = $this->action;
        $data['catalogId'] = $this->catalogId;
        $data['modTitle'] = $this->title;
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
        return Catalog::instance()->getCatalogPathById($this->catalogId);
    }

    protected function usePageHelper($total,$pageKeyName = 'page',$limit = 20 , $query = null): Pagination
    {
        $this->pageHelper = new Pagination(intval(Request::get($pageKeyName)),$limit, $query ?? Request::get());
        $this->pageHelper->setTotal($total);
        View::share('page',$this->pageHelper);
        return $this->pageHelper;
    }

    protected function getTotalRows($conditions)
    {
        $query = DB::table('node_relation','nr')
            ->leftJoin(['node','n'],'nr.node_id=n.id')
            ->select('count(n.id) as rowcount');

        $this->prepareConditions($query,$conditions);
        return $query->fetchColumn();
    }

    protected function getAll($conditions)
    {

        $query = DB::table('node_relation','nr')
            ->leftJoin(['node','n'],'nr.node_id=n.id');
        $query->select('n.*');
        $this->prepareConditions($query,$conditions);
       return $query->get(FETCH_ASSOC);
    }

    protected function getNodeRelationships($node_id){
        return NodeRelation::find(['node_id'=>$node_id])
            ->select('catalog_id,node_id')
            ->get(FETCH_KEY_PAIR);
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

}
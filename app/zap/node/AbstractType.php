<?php

namespace zap\node;

use zap\Auth;
use zap\Catalog;
use zap\DB;
use zap\http\Request;
use zap\NodeRelation;
use zap\NodeType;
use zap\util\Str;
use zap\view\View;

class AbstractType
{
    protected $nodeType = NodeType::NEWS;

    protected $catalogId;

    public $controller;
    public $action;

    protected $title;

    public function __construct()
    {
        $this->catalogId = intval(Request::get('catalog_id'));
    }

    public function __init(){

    }

    public function getTitle($msg): string
    {
        return sprintf($msg,$this->title);
    }

    public function display($data = [], $name = null){
        $controller = strtolower(trim(preg_replace('/([A-Z])/', '-$1', $this->controller),'-'));
        if(is_null($name)){
            $action = strtolower(trim(preg_replace('/([A-Z])/', '-$1', $this->action),'-'));
            $name = "{$controller}.{$action}";
        }else{
            $name = "{$controller}.{$name}";
        }
        $data['_controller'] = $this->controller;
        $data['_action'] = $this->action;
        $data['catalogId'] = $this->catalogId;
        View::render($name,$data);
    }

    /**
     * @return int
     */
    public function getCatalogById($id)
    {
        return Catalog::instance()->get($id);
    }

    public function getCurrentCatalogPath(){
        return Catalog::instance()->getCatalogPathById($this->catalogId);
    }

    public function getAllTotalRows($conditions)
    {
        return DB::table('node_relation','nr')
            ->leftJoin(['node','n'],'nr.node_id=n.id')
            ->select('count(n.id) as rowcount')
            ->where('nr.catalog_id',$conditions['catalog_id'])
            ->where('n.node_type',$conditions['node_type'])
            ->where('n.author_id',Auth::user('id'))
            ->fetchColumn();
    }

    public function getAll($conditions)
    {

        return DB::table('node_relation','nr')
            ->leftJoin(['node','n'],'nr.node_id=n.id')
            ->select('n.*')
            ->where('nr.catalog_id',$conditions['catalog_id'])
            ->where('n.node_type',$conditions['node_type'])
            ->where('n.author_id',Auth::user('id'))
            ->orderBy('id desc')
            ->limit(...$conditions['limit'])
            ->get(FETCH_ASSOC);
    }

    public function getNodeRelationships($node_id){
        return NodeRelation::find(['node_id'=>$node_id])
            ->select('catalog_id,node_id')
            ->get(FETCH_KEY_PAIR);
    }

}
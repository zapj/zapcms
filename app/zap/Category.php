<?php

namespace zap;

/**
 * Category
 *
 *
 * CREATE TABLE `zap_category` (
 * `id` int(11) NOT NULL,
 * `name` varchar(255) NOT NULL,
 * `path` varchar(255) DEFAULT NULL,
 * `pid` int(11) NOT NULL,
 * `level` int(11) DEFAULT '0',
 * `sort_order` int(11) DEFAULT '0'
 * ) ENGINE=MyISAM DEFAULT CHARSET=utf8;
 *
 *
 *
 *
 * @table Table Schema
 *
 */
class Category
{

    protected static $instance;
    //表名
    protected $table;
    //分类Path
    protected $pathColumn = 'path';
    //父级ID列名
    protected $parentColumn = 'pid';
    protected $primaryKey = 'id';

    //分类层级
    protected $levelColumn = 'level';

    public function __construct($table, $primaryKey = 'id', $parentColumn = 'pid', $pathColumn = 'path', $levelColumn = 'level')
    {
        $this->table = $table;
        $this->primaryKey = $primaryKey;
        $this->parentColumn = $parentColumn;
        $this->pathColumn = $pathColumn;
        $this->levelColumn = $levelColumn;
    }


    public function add($data, $pid)
    {
        if ($pid > 0) {
            $parent = DB::table($this->table)->where('id', $pid)->fetch();
//            $data[$this->pathColumn] = sprintf('%s,%s',$parent->path,$parent->id);
            $data[$this->pathColumn] = $parent->path;
            $data[$this->levelColumn] = $parent->level + 1;
        } else {
            $data[$this->pathColumn] = '';
            $data[$this->levelColumn] = 1;
        }

        $data[$this->parentColumn] = $pid;
        $category_id = DB::insert($this->table, $data);
        if($pid == 0){
            DB::update($this->table,[$this->pathColumn => $category_id],[$this->primaryKey => $category_id]);
        }else{
            DB::update($this->table,[
                $this->pathColumn => sprintf("%s,%s",$data[$this->pathColumn],$category_id)
            ],[$this->primaryKey => $category_id]);
        }
        return $category_id;
    }

    public function update($data,$id){
        $category = $this->get($id);
        if(is_null($category)){
            return false;
        }
        //修改父类 需要更新
        if($data[$this->parentColumn] != $category[$this->parentColumn]){
            $parent = $this->get($data[$this->parentColumn]);
            $data[$this->pathColumn] = "{$parent[$this->pathColumn]},{$id}";

            //更新下级所有菜单 path
            $pathPrefix = $category[$this->pathColumn];
            DB::table($this->table)->where($this->pathColumn,'LIKE',"{$pathPrefix},%")
                ->set($this->pathColumn,DB::raw("REPLACE({$this->pathColumn},'{$pathPrefix}','{$data[$this->pathColumn]}')"))
                ->update();
        }
        DB::update($this->table,$data,['id'=>$id]);
    }

    public function remove($id){
        $category = DB::table($this->table)->where($this->primaryKey, $id)->fetch(FETCH_ASSOC);
        if(is_null($category)){
            return false;
        }
        $path = $category[$this->pathColumn];
        //删除子类
        DB::table($this->table)->where($this->pathColumn,'LIKE',"{$path},{$id}%")->delete();
        return DB::table($this->table)->where($this->primaryKey,$id)->delete();
    }


    public function getTreeArray(): array
    {
        $data = DB::table($this->table)
            ->orderBy("{$this->parentColumn} ASC,sort_order ASC")
            ->get(FETCH_ASSOC);
        $categories = array();
        foreach ($data as $value) {
            $categories[$value[$this->primaryKey]] = $value;
        }

        $tree = array();
        foreach ($categories as $id => $item) {
            if (isset($categories[$item[$this->parentColumn]])) {
                $categories[$item[$this->parentColumn]]['children'][] = &$categories[$id];
            } else {
                $tree[] = &$categories[$id];
            }
        }
        return $tree;

    }

    public function forEachAll($callback)
    {
        $categories = $this->getTreeArray();
        while($data = array_shift($categories)){
            $callback($data);
            if(!empty($data['children'])){
                while($children = array_pop($data['children'])){
                    array_unshift($categories,$children);
                }
//                foreach ($data['children'] as $child){
//                    array_unshift($categories,$child);
//                }

            }
        }
    }

    public function get($id){
        return DB::table($this->table)->where($this->primaryKey, $id)->fetch(FETCH_ASSOC);
    }

    /**
     * @return Category
     */
    public static function instance(): Category
    {
        if(is_null(static::$instance)){
            static::$instance = new static;
        }
        return static::$instance;
    }


}
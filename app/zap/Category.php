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
    //表名
    protected $table;
    //分类Path
    protected $pathColumn = 'path';
    //父级ID列名
    protected $parentColumn = 'pid';

    protected $primaryKey = 'id';
    //分类层级
    protected $levelColumn = 'level';

    protected $defaultLevel = 0;

    public function __construct($table, $primaryKey = 'id', $parentColumn = 'pid', $pathColumn = 'path', $levelColumn = 'level')
    {
        $this->table = $table;
        $this->primaryKey = $primaryKey;
        $this->parentColumn = $parentColumn;
        $this->pathColumn = $pathColumn;
        $this->levelColumn = $levelColumn;
    }


    public function add($data)
    {
        $data[$this->parentColumn] = $data[$this->parentColumn] ?? 0;
        if ($data[$this->parentColumn] > 0) {
            $parent = DB::table($this->table)->where($this->primaryKey, $data[$this->parentColumn])->fetch();
//            $data[$this->pathColumn] = sprintf('%s,%s',$parent->path,$parent->id);
            $data[$this->pathColumn] = $parent->path;
            $data[$this->levelColumn] = $parent->level + 1;
        } else {
            $data[$this->pathColumn] = '';
            $data[$this->levelColumn] = $this->defaultLevel;
        }

//        $data[$this->parentColumn] = $data['pid'];
        $category_id = DB::insert($this->table, $data);
        if($data[$this->parentColumn] == 0){
            DB::update($this->table,[$this->pathColumn => "{$category_id},"],[$this->primaryKey => $category_id]);
        }else{
            DB::update($this->table,[
                $this->pathColumn => sprintf("%s%s,",$data[$this->pathColumn],$category_id)
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


            if($data[$this->parentColumn] == 0){
                $data[$this->pathColumn] = ",{$id},";
            }else{
                $parent = $this->get($data[$this->parentColumn]);
                $data[$this->pathColumn] = "{$parent[$this->pathColumn]}{$id},";
            }
            //更新下级所有菜单 path
            $pathPrefix = $category[$this->pathColumn];
            $childrenCategories = DB::table($this->table)->where($this->pathColumn,'LIKE',"{$pathPrefix}%")
                ->get(FETCH_ASSOC);
            foreach ($childrenCategories as $row){
                $path = str_replace($pathPrefix,$data[$this->pathColumn],$row[$this->pathColumn]);
                $level = substr_count($path,',');
                DB::table($this->table)->where($this->primaryKey,$row[$this->primaryKey])
                    ->set($this->pathColumn,$path)
                    ->set($this->levelColumn,$level < 1 ? 0 : $level - 1)
                    ->update();
                //->set($this->pathColumn,DB::raw("REPLACE({$this->pathColumn},'{$pathPrefix}','{$data[$this->pathColumn]}')"))
            }

        }
        DB::update($this->table,$data,[$this->primaryKey=>$id]);
    }

    public function remove($id){
        $category = DB::table($this->table)->where($this->primaryKey, $id)->fetch(FETCH_ASSOC);
        if(is_null($category)){
            return false;
        }
        $path = $category[$this->pathColumn];
        //删除子类
        DB::table($this->table)->where($this->pathColumn,'LIKE',"{$path}%")->delete();
        return DB::table($this->table)->where($this->primaryKey,$id)->delete();
    }


    public function getTreeArray($conditions = []): array
    {
        $query = DB::table($this->table)
            ->orderBy("{$this->parentColumn} ASC,sort_order ASC");
        foreach ($conditions as $name=>$value){
            if(is_int($name)){
                $query->where(...$value);
            }else{
                $query->where($name,$value);
            }
        }
        $data = $query->get(FETCH_ASSOC);
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

    public function getAll($conditions){
        $query = DB::table($this->table);
        foreach ($conditions as $name=>$value){
            if(is_int($name)){
                $query->where(...$value);
            }else{
                $query->where($name,$value);
            }
        }
        return $query->fetchAll(FETCH_ASSOC);
    }

    public function getAllByPath($conditions = []){
        $query = DB::table($this->table);
        foreach ($conditions['where'] ?? [] as $name=>$value){
            if(is_int($name)){
                $query->where(...$value);
            }else{
                $query->where($name,$value);
            }
        }
        if(isset($conditions['count'])){
            return $query->count($this->primaryKey);
        }
        $query->orderBy('path ASC');
        if(isset($conditions['limit'])){
            $query->limit(...$conditions['limit']);
        }
        return $query->fetchAll(FETCH_ASSOC);
    }

    public function setDefaultLevel($level){
        $this->defaultLevel = $level;
    }

}
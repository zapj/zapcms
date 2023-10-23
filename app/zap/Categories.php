<?php

namespace zap;

/**
 * Categories
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
class Categories
{
    //表名
    protected $table;

    protected $path_table;
    //分类Path
    protected $pathColumn = 'path';
    //父级ID列名
    protected $parentColumn = 'pid';

    protected $primaryKey = 'id';
    //分类层级
    protected $levelColumn = 'level';

    protected $defaultLevel = 0;

    public function __construct($table,$path_table, $primaryKey = 'id', $parentColumn = 'pid', $pathColumn = 'path', $levelColumn = 'level')
    {
        $this->table = $table;
        $this->path_table = $path_table;
        $this->primaryKey = $primaryKey;
        $this->parentColumn = $parentColumn;
        $this->pathColumn = $pathColumn;
        $this->levelColumn = $levelColumn;
    }


    public function add($data)
    {
        $data[$this->parentColumn] = $data[$this->parentColumn] ?? 0;
        $data[$this->pathColumn] = '';
        $data[$this->levelColumn] = $this->defaultLevel;

        if ($data[$this->parentColumn] > 0) {
            $parent = $this->get($data[$this->parentColumn]);
            $data[$this->pathColumn] = $parent[$this->pathColumn];
            $data[$this->levelColumn] = $parent[$this->levelColumn] + 1;
        }

        $category_id = DB::insert($this->table, $data);
        $data[$this->pathColumn] = "{$data[$this->pathColumn]}{$category_id},";
        DB::update($this->table,[$this->pathColumn => $data[$this->pathColumn] ],[$this->primaryKey => $category_id]);
        $path_ids = array_filter(explode(',',$data[$this->pathColumn]));
        foreach ($path_ids as $level=>$path_id){
            DB::insert($this->path_table,['taxonomy'=>$this->table,'taxonomy_id'=>$category_id,'path_id'=>$path_id,'level'=>$level]);
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
        DB::table($this->path_table)->where('taxonomy',$this->table)->where('path_id',$id)->delete();
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

    public function getPaths($category_id){
        return DB::table($this->path_table)
            ->where('taxonomy', $this->table)
            ->where('taxonomy_id', $category_id)
            ->orderBy('level ASC')
            ->fetchAll(FETCH_ASSOC);
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
//SELECT GROUP_CONCAT(c2.`title` ORDER BY cp.`level` SEPARATOR ' > ') AS `name`,cp.`perm_id`,  c2.`pid`,cp.level FROM zap_permissions_path cp
//LEFT JOIN zap_permissions c2 ON (cp.`path_id` = c2.`perm_id`)
    public function getAllByPath($conditions){

        $query = DB::table($this->path_table,'tp');
        $query->where('tp.taxonomy',$this->table);
        foreach ($conditions['where'] ?? [] as $name=>$value){
            if(is_int($name)){
                $query->where(...$value);
            }else{
                $query->where($name,$value);
            }
        }
        if(isset($conditions['count'])){
            return $query->count();
        }
        $query->leftJoin([$this->table,'p'],"tp.taxonomy_id=p.{$this->primaryKey}");
        $query->leftJoin([$this->table,'pp'],"tp.path_id=pp.{$this->primaryKey}");
        if($query->driver == 'mysql'){
            DB::rawExec("SET sql_mode='';");
            $query->select(["GROUP_CONCAT(pp.`title` ORDER BY tp.`level` SEPARATOR ' > ') AS `title`",
                "tp.taxonomy_id {$this->primaryKey}",
                "p.perm_key",
                "p.description",
                "p.updated_at",
                "p.created_at",
                "tp.level"
            ]);
        }
        $query->groupBy("tp.taxonomy_id");
//        $query->orderBy("p.perm_id DESC");
        $query->orderBy("p.updated_at DESC");
        if(isset($conditions['limit'])){
            $query->limit(...$conditions['limit']);
        }
//        echo $query->getFullSQL();
        return $query->fetchAll(FETCH_ASSOC);
    }

    public function setDefaultLevel($level){
        $this->defaultLevel = $level;
    }

}
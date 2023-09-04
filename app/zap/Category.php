<?php

namespace zap;

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
            $data[$this->pathColumn] = sprintf('%s-%s',$parent->path,$parent->id);
            $data[$this->levelColumn] = $parent->level + 1;
        } else {
            $data[$this->pathColumn] = '0';
            $data[$this->levelColumn] = 0;
        }

        $data[$this->parentColumn] = $pid;
        return DB::insert($this->table, $data);
    }

    public function update($data,$id){
        $category = DB::table($this->table)->where($this->primaryKey, $id)->fetch(FETCH_ASSOC);
        if(is_null($category)){
            return false;
        }
        $path = $category[$this->pathColumn];
        DB::table($this->table)->where($this->pathColumn,'LIKE',"{$path}%")->update();
        DB::update($this->table,$data,['id'=>$id]);
    }

    public function remove($id){
        $category = DB::table($this->table)->where($this->primaryKey, $id)->fetch(FETCH_ASSOC);
        if(is_null($category)){
            return false;
        }
        $path = $category[$this->pathColumn];
        DB::table($this->table)->where($this->pathColumn,'LIKE',"{$path}%")->delete();
        return DB::table($this->table)->where($this->primaryKey,$id)->delete();
    }


    public function getAll()
    {
        $categories = DB::table($this->table)
            ->orderBy("{$this->parentColumn} ASC,sort_order DESC")
            ->get(FETCH_ASSOC);
//        return $this->generateTree($categories);
    }


    public function getTreeArray()
    {
        $data = DB::table($this->table)
            ->orderBy("{$this->parentColumn} ASC,sort_order DESC")
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
                foreach ($data['children'] as $child){
                    array_unshift($categories,$child);
                }

            }
        }
    }


}
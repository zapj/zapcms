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

    //分类层级
    protected $levelColumn = 'level';
    public function __construct($table){
        $this->table = $table;
    }


    public function add($data,$pid){
        if($pid > 0){
           $parent = DB::table($this->table)->where('id',$pid)->fetch();
           $data[$this->pathColumn] = "{$parent->path}-{$parent->id}";
           $data[$this->levelColumn] = $parent->level + 1;
        }else{
            $data[$this->pathColumn] = '0';
            $data[$this->levelColumn] = 0;
        }

        $data[$this->parentColumn] = $pid;
        return DB::insert($this->table,$data);
    }


}
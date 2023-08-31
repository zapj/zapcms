<?php

namespace zap;

class Option
{
    public static function update($option_name,$option_value,$sort_order = 0,$autoload = 0){
        return DB::replace('options',['option_name'=>$option_name,'option_value'=>$option_value,'sort_order'=>$sort_order,'autoload'=>$autoload]);
    }

    public static function add($option_name,$option_value,$sort_order = 0,$autoload = 0){
        try {
            return DB::insert('options',['option_name'=>$option_name,'option_value'=>$option_value,'sort_order'=>$sort_order,'autoload'=>$autoload]);
        }catch (\PDOException $e){
            return DB::update('options',['option_name'=>$option_name,'option_value'=>$option_value,'sort_order'=>$sort_order,'autoload'=>$autoload]);
        }
    }

    public static function addArray($options,$sort_order = 0,$autoload = 0){

        foreach ($options as $option_name=>$option_value){
            DB::insert('options',['option_name'=>$option_name,'option_value'=>$option_value,'sort_order'=>$sort_order,'autoload'=>$autoload]);
            $sort_order++;
        }
    }

    public static function remove($option_name){
        return DB::delete('options','option_name=:name',['name'=>$option_name]);
    }

    public static function get($option_name,$default = null){
        $option_value = DB::scalar('select option_value from {options} where option_name=:option_name',['option_name'=>$option_name]);
        if(empty($option_value)){
            return $default;
        }
        return $option_value;
    }

    public static function getArray($option_name,$default = []){
        $options = DB::table('options')->select('option_name,option_value')
            ->where('option_name','LIKE',DB::raw($option_name))
            ->orderBy('sort_order desc')
            ->fetchAll(FETCH_KEY_PAIR);
        if(empty($options)){
            return $default;
        }
        return $options;
    }
}
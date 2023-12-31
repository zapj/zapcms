<?php
/*
 * Copyright (c) 2023.  ZAP.CN  - ZAP CMS - All Rights Reserved
 * @author Allen
 * @email zapcms@zap.cn
 * @date 2023/12/27 上午11:26
 * @lastModified 2023/12/14 上午11:55
 *
 */

namespace zap\cms;


use zap\DB;

class Option
{
    public static function update($option_name,$option_value,$sort_order = null,$autoload = null){
        $data = ['option_value'=>$option_value];
        is_null($sort_order) ?: $data['sort_order'] = $sort_order;
        is_null($autoload) ?: $data['autoload'] = $autoload;
        return DB::update('options',$data,['option_name'=>$option_name]);
    }

    public static function replace($option_name,$option_value,$sort_order = null,$autoload = null){
        $data = ['option_name'=>$option_name,'option_value'=>$option_value];
        is_null($sort_order) ?: $data['sort_order'] = $sort_order;
        is_null($autoload) ?: $data['autoload'] = $autoload;
        return DB::replace('options',$data);
    }

    public static function add($option_name,$option_value,$sort_order = null,$autoload = null){
        $data = ['option_name'=>$option_name,'option_value'=>$option_value];
        is_null($sort_order) ?: $data['sort_order'] = $sort_order;
        is_null($autoload) ?: $data['autoload'] = $autoload;
        try {
            return DB::insert('options',$data);
        }catch (\PDOException $e){
            return DB::update('options',$data);
        }
    }

    public static function addArray($options,$sort_order = null,$autoload = null){
        $data = [];
        is_null($sort_order) ?: $data['sort_order'] = $sort_order;
        is_null($autoload) ?: $data['autoload'] = $autoload;
        foreach ($options as $option_name=>$option_value){
            $data['option_name'] = $option_name;
            $data['option_value'] = $option_value;
            DB::insert('options',$data);
        }
    }

    public static function remove($option_name){
        return DB::delete('options','option_name=:name',['name'=>$option_name]);
    }


    public static function get($option_name, $default = null){
        $option_value = DB::value('select option_value from {options} where option_name=:option_name',
            ['option_name'=>$option_name]);
        if(empty($option_value)){
            return $default;
        }
        return $option_value;
    }

    public static function getArray($option_name,$type = '='): array
    {
        if(is_array($option_name) && $type == 'REGEXP'){
            $option_name = join('|',$option_name);
            $option_name = "^({$option_name}).*";
        }else if(is_string($option_name) && $type == 'REGEXP'){
            $option_name = "^({$option_name}).*";
        }

        $query = DB::table('options')->select('option_name,option_value')
            ->orderBy('sort_order desc');
        $query->where('option_name',$type,$option_name);
        $options = $query->fetchAll(FETCH_KEY_PAIR);
        if(empty($options)){
            return [];
        }
        return $options;
    }

    public static function getKeys($option_name,$type = '='): array
    {
        if(is_array($option_name) && $type == 'REGEXP'){
            $option_name = join('|',$option_name);
            $option_name = "^({$option_name}).*";
        }else if(is_string($option_name) && $type == 'REGEXP'){
            $option_name = "^({$option_name}).*";
        }

        $query = DB::table('options')->select('option_name')
            ->orderBy('sort_order desc');

        $query->where('option_name',$type,$option_name);
        $options = $query->fetchAll(FETCH_COLUMN);
        if(empty($options)){
            return [];
        }
        return $options;
    }
}